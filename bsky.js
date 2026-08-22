/**
 * bsky.js — Studio Anni homepage Bluesky widget.
 * No build step, no dependencies (HLS.js is optional, see renderVideo).
 * Pulls data from bsky-proxy.php, which holds the app password server-side.
 */
(function () {
  const CONTAINER_ID = 'bsky-widget';
  const PROXY_URL = 'bsky-proxy.php';

  let posts = [];
  let currentIndex = 0;

  function fmtDate(iso) {
    if (!iso) return '';
    const d = new Date(iso);
    return isNaN(d) ? '' : d.toLocaleString();
  }

  function el(tag, className, html) {
    const node = document.createElement(tag);
    if (className) node.className = className;
    if (html !== undefined) node.innerHTML = html;
    return node;
  }

  function renderVideo(video) {
    if (!video || !video.playlist) return null;
    const wrap = el('div', 'bsky-video-wrap');
    const videoEl = document.createElement('video');
    videoEl.controls = true;
    videoEl.playsInline = true;
    videoEl.poster = video.thumbnail || '';
    videoEl.className = 'bsky-video';

    if (videoEl.canPlayType('application/vnd.apple.mpegurl')) {
      // Safari plays HLS natively.
      videoEl.src = video.playlist;
      wrap.appendChild(videoEl);
    } else if (window.Hls && window.Hls.isSupported()) {
      // Everyone else needs hls.js — see the note at the bottom of this file.
      wrap.appendChild(videoEl);
      const hls = new window.Hls();
      hls.loadSource(video.playlist);
      hls.attachMedia(videoEl);
    } else {
      const fallback = el('p', 'bsky-metatext');
      fallback.textContent = 'Video unavailable in this browser — ';
      const link = document.createElement('a');
      link.href = video.playlist;
      link.target = '_blank';
      link.rel = 'noreferrer';
      link.textContent = 'view it on Bluesky instead.';
      fallback.appendChild(link);
      wrap.appendChild(fallback);
    }
    return wrap;
  }

  function renderImages(images) {
    if (!images || !images.length) return null;
    const group = el('div', 'bsky-media-group');
    images.forEach((img) => {
      const item = el('div', 'bsky-img-item');
      const image = document.createElement('img');
      image.src = img.fullsize;
      image.alt = img.alt || 'No ALT text provided for this image.';
      image.className = 'bsky-img-file';
      item.appendChild(image);
      const alt = el('p', 'bsky-metatext');
      alt.textContent = '// ALT TEXT: ' + (img.alt || '<none provided>');
      item.appendChild(alt);
      group.appendChild(item);
    });
    return group;
  }

  function renderExternal(external) {
    if (!external || !external.uri) return null;

    if (external.isYoutube && external.youtubeId) {
      const wrap = el('div', 'bsky-youtube-wrap');
      const iframe = document.createElement('iframe');
      iframe.src = `https://www.youtube.com/embed/${external.youtubeId}`;
      iframe.width = '100%';
      iframe.height = '315';
      iframe.title = external.title || 'YouTube video';
      iframe.allowFullscreen = true;
      iframe.className = 'bsky-youtube';
      wrap.appendChild(iframe);
      if (external.title || external.description) {
        wrap.appendChild(renderWebDeets(external));
      }
      return wrap;
    }

    const card = el('div', 'bsky-web-card');
    const link = document.createElement('a');
    link.href = external.uri;
    link.target = '_blank';
    link.rel = 'noreferrer';
    if (external.thumb) {
      const img = document.createElement('img');
      img.src = external.thumb;
      img.alt = external.title || 'Link preview';
      img.className = 'bsky-webcard-img';
      link.appendChild(img);
    }
    card.appendChild(link);
    card.appendChild(renderWebDeets(external));
    return card;
  }

  function renderWebDeets(external) {
    const deets = el('div', 'bsky-web-deets');
    if (external.title) {
      const h4 = document.createElement('h4');
      h4.textContent = external.title;
      deets.appendChild(h4);
    }
    if (external.description) {
      const p = el('p', 'bsky-metatext');
      p.textContent = external.description;
      deets.appendChild(p);
    }
    return deets;
  }

  function renderContentWarning(labels) {
    const wrap = el('div', 'bsky-cw');
    const p = document.createElement('p');
    const labelText = labels && labels.length ? labels.join(', ') : 'sensitive content';
    p.textContent = `This post is flagged for: ${labelText}. View it directly on Bluesky to see the full content.`;
    wrap.appendChild(p);
    return wrap;
  }

  function renderMedia(node, { hasContentWarning, contentWarningLabels, images, video, external }) {
    if (hasContentWarning) {
      node.appendChild(renderContentWarning(contentWarningLabels));
      return;
    }
    const media = renderImages(images);
    if (media) node.appendChild(media);
    const vid = renderVideo(video);
    if (vid) node.appendChild(vid);
    const ext = renderExternal(external);
    if (ext) node.appendChild(ext);
  }

  function renderQuote(quote) {
    if (!quote) return null;
    const box = el('div', 'bsky-quote-box');

    const header = el('div', 'bsky-quote-header');
    if (quote.avatar) {
      const avatar = document.createElement('img');
      avatar.src = quote.avatar;
      avatar.alt = `${quote.handle}'s avatar`;
      avatar.className = 'bsky-author-avatar';
      header.appendChild(avatar);
    }
    const nameSpan = el('span', 'bsky-quote-author');
    nameSpan.appendChild(document.createTextNode('Quoting '));
    const handleLink = document.createElement('a');
    handleLink.href = `https://bsky.app/profile/${quote.handle}`;
    handleLink.target = '_blank';
    handleLink.rel = 'noreferrer';
    handleLink.textContent = '@' + quote.handle;
    nameSpan.appendChild(handleLink);
    header.appendChild(nameSpan);
    box.appendChild(header);

    box.appendChild(el('div', 'bsky-quote-text', quote.textHtml));
    renderMedia(box, quote);

    return box;
  }

  function renderPost(post) {
    const section = el('article', 'bsky-post');

    const header = el('div', 'bsky-header');
    if (post.avatar) {
      const avatar = document.createElement('img');
      avatar.src = post.avatar;
      avatar.alt = `${post.handle}'s Bluesky avatar`;
      avatar.className = 'bsky-author-avatar';
      header.appendChild(avatar);
    }
    const postUrl = `https://bsky.app/profile/${post.handle}/post/${post.uri.split('/').pop()}`;
    const meta = el('span', 'bsky-date');
    meta.innerHTML =
      `Via <a href="https://bsky.app/profile/${post.handle}" target="_blank" rel="noreferrer">@${post.handle}</a><br>` +
      `<a href="${postUrl}" target="_blank" rel="noreferrer"><u>${fmtDate(post.createdAt)}</u></a>`;
    header.appendChild(meta);
    section.appendChild(header);

    const body = el('div', 'bsky-body');

    if (post.replyToHandle) {
      const reply = el('div', 'bsky-reply-status');
      reply.textContent = `Replying to @${post.replyToHandle}...`;
      body.appendChild(reply);
    }

    body.appendChild(el('div', 'bsky-text', post.textHtml));
    renderMedia(body, post);

    const quote = renderQuote(post.quote);
    if (quote) body.appendChild(quote);

    const stats = el('div', 'bsky-stats');
    const score = el('div', 'bsky-score');
    stats.innerHTML = `
      <span class="bsky-stat">❤ ${post.counts.likes}</span>
      <span class="bsky-stat">🔁 ${post.counts.reposts}</span>
      <span class="bsky-stat">💬 ${post.counts.replies}</span>
      <span class="bsky-stat">RCB Score: <span class="bsky-score-value">${post.rcbScore}</span></span>
    `;
    body.appendChild(stats);
    body.appendChild(score);

    section.appendChild(body);
    return section;
  }

  function renderPagination(root) {
    const controls = el('div', 'bsky-pagination');
    const prev = el('button', 'bsky-pag-button', 'Previous');
    const next = el('button', 'bsky-pag-button', 'Next');
    prev.type = 'button';
    next.type = 'button';

    // posts[0] is the newest post. Previous steps further into the array
    // (older posts); Next steps back toward index 0 (newer posts) and is
    // only active once you've paginated away from the newest post.
    prev.disabled = currentIndex >= posts.length - 1;
    next.disabled = currentIndex === 0;

    prev.addEventListener('click', () => {
      currentIndex = Math.min(posts.length - 1, currentIndex + 1);
      prev.blur();
      render(root);
    });
    next.addEventListener('click', () => {
      currentIndex = Math.max(0, currentIndex - 1);
      next.blur();
      render(root);
    });

    controls.appendChild(prev);
    controls.appendChild(next);
    return controls;
  }

  function render(root) {
    root.innerHTML = '';
    if (!posts.length) {
      root.appendChild(el('p', 'bsky-loading', 'No posts to show right now.'));
      return;
    }
    root.appendChild(renderPost(posts[currentIndex]));
    root.appendChild(renderPagination(root));
    const follow = el('p', 'bsky-follow');
    follow.innerHTML = 'Follow <a href="https://bsky.app/profile/studioanni.com" target="_blank" rel="noreferrer">@studioanni.com</a> on Bluesky!';
    root.appendChild(follow);
  }

  function init() {
    const root = document.getElementById(CONTAINER_ID);
    if (!root) return;
    root.innerHTML = '<p class="bsky-loading">Loading posts from the ATmosphere...</p>';

    fetch(PROXY_URL, { credentials: 'same-origin' })
      .then((res) => {
        if (!res.ok) throw new Error('Bad response from proxy');
        return res.json();
      })
      .then((data) => {
        if (data.error) throw new Error(data.error);
        posts = data.posts || [];
        currentIndex = 0;
        render(root);
      })
      .catch((err) => {
        console.error('Bluesky widget error:', err);
        root.innerHTML = '<p class="bsky-loading">Unable to load the Bluesky feed right now. :(</p>';
      });
  }

  document.addEventListener('DOMContentLoaded', init);
})();