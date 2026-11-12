(() => {
  'use strict';

  const $ = (selector, root = document) => root.querySelector(selector);
  const $$ = (selector, root = document) => Array.from(root.querySelectorAll(selector));

  const sidebar = $('#sidebar');
  const openSidebar = () => {
    document.body.classList.add('sidebar-open');
    sidebar?.setAttribute('aria-expanded', 'true');
  };
  const closeSidebar = () => {
    document.body.classList.remove('sidebar-open');
    sidebar?.setAttribute('aria-expanded', 'false');
  };
  $('[data-sidebar-toggle]')?.addEventListener('click', openSidebar);
  $('[data-sidebar-close]')?.addEventListener('click', closeSidebar);
  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') closeSidebar();
    if ((event.metaKey || event.ctrlKey) && event.key.toLowerCase() === 'k') {
      const search = $('[data-library-search]');
      if (search) {
        event.preventDefault();
        search.focus();
      }
    }
  });

  $$('[data-toast]').forEach((toast) => {
    $('[data-toast-close]', toast)?.addEventListener('click', () => toast.remove());
    window.setTimeout(() => {
      toast.classList.add('toast--leaving');
      window.setTimeout(() => toast.remove(), 350);
    }, toast.textContent.includes('Mot de passe temporaire') ? 18000 : 6800);
  });
  $$('[data-toast-close]').forEach((button) => button.addEventListener('click', () => button.closest('.toast')?.remove()));

  $$('[data-password-toggle]').forEach((button) => {
    button.addEventListener('click', () => {
      const input = button.parentElement?.querySelector('input');
      if (!input) return;
      const showing = input.type === 'text';
      input.type = showing ? 'password' : 'text';
      button.setAttribute('aria-label', showing ? 'Afficher le mot de passe' : 'Masquer le mot de passe');
      input.focus();
    });
  });

  $$('form[data-confirm]').forEach((form) => {
    form.addEventListener('submit', (event) => {
      const prompt = form.getAttribute('data-confirm') || 'Confirmer cette action ?';
      if (!window.confirm(prompt)) event.preventDefault();
    });
  });

  $$('[data-submit-button]').forEach((button) => {
    button.closest('form')?.addEventListener('submit', () => {
      button.classList.add('is-loading');
      button.setAttribute('aria-busy', 'true');
      const label = button.querySelector('span');
      if (label) label.textContent = 'Enregistrement en cours…';
    });
  });

  $$('[data-color-hex]').forEach((hexInput) => {
    const colorInput = hexInput.closest('.color-input')?.querySelector('input[type="color"]');
    if (!colorInput) return;
    colorInput.addEventListener('input', () => { hexInput.value = colorInput.value.toUpperCase(); });
    hexInput.addEventListener('input', () => {
      if (/^#[0-9a-f]{6}$/i.test(hexInput.value)) colorInput.value = hexInput.value;
    });
    hexInput.addEventListener('blur', () => {
      if (!/^#[0-9a-f]{6}$/i.test(hexInput.value)) hexInput.value = colorInput.value.toUpperCase();
    });
  });

  $$('[data-image-preview]').forEach((input) => {
    input.addEventListener('change', () => {
      const file = input.files?.[0];
      if (!file || !file.type.startsWith('image/')) return;
      const preview = input.closest('.upload-drop')?.querySelector('img');
      if (preview) preview.src = URL.createObjectURL(file);
    });
  });

  const library = $('[data-library]');
  if (library) {
    const form = $('[data-library-form]', library);
    const grid = $('[data-prototype-grid]', library);
    const skeletons = $('[data-skeleton-grid]', library);
    const emptyState = $('[data-library-empty]', library);
    const pagination = $('[data-pagination]', library);
    const paginationPages = $('[data-pagination-pages]', library);
    const resultCount = $('[data-result-count]', library);
    const searchInput = $('[data-library-search]', library);
    const clearButton = $('[data-clear-filters]', library);
    const apiUrl = library.dataset.apiUrl;
    const isAdmin = library.dataset.admin === '1';
    const token = library.dataset.csrfToken || '';
    const basePath = apiUrl.replace(/\/api\/prototypes\/?$/, '');
    let requestController = null;
    let searchTimer = null;
    let currentPage = Number(library.dataset.currentPage || 1);

    const appendText = (parent, tagName, className, text) => {
      const element = document.createElement(tagName);
      if (className) element.className = className;
      element.textContent = text || '';
      parent.append(element);
      return element;
    };
    const makeIcon = (name) => {
      const wrapper = document.createElement('span');
      wrapper.className = 'mini-icon mini-icon--' + name;
      const shapes = {
        arrow: '<path d="M7 17 17 7M7 7h10v10"/>',
        clock: '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        right: '<path d="M5 12h14m-7-7 7 7-7 7"/>',
        external: '<path d="M14 3h7v7M10 14 21 3"/><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>',
        edit: '<path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L8 18l-4 1 1-4Z"/>',
        trash: '<path d="M3 6h18M8 6V4h8v2m3 0-1 14H6L5 6m4 4v6m6-6v6"/>',
      };
      const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
      svg.setAttribute('viewBox', '0 0 24 24');
      svg.setAttribute('width', '16');
      svg.setAttribute('height', '16');
      svg.setAttribute('fill', 'none');
      svg.setAttribute('stroke', 'currentColor');
      svg.setAttribute('stroke-width', '1.7');
      svg.setAttribute('stroke-linecap', 'round');
      svg.setAttribute('stroke-linejoin', 'round');
      svg.setAttribute('aria-hidden', 'true');
      svg.innerHTML = shapes[name] || shapes.arrow;
      wrapper.append(svg);
      return wrapper;
    };
    const safeLink = (href, label, className, external = false) => {
      const link = document.createElement('a');
      link.href = href;
      link.className = className;
      link.textContent = label;
      if (external) {
        link.target = '_blank';
        link.rel = 'noopener noreferrer';
      }
      return link;
    };
    const makeCard = (item) => {
      const card = document.createElement('article');
      card.className = 'prototype-card';
      const cover = safeLink(item.url, '', 'prototype-card__cover', true);
      cover.setAttribute('aria-label', 'Ouvrir ' + item.name + ' dans un nouvel onglet');
      const image = document.createElement('img');
      image.src = item.cover_url;
      image.alt = 'Aperçu de ' + item.name;
      image.loading = 'lazy';
      image.addEventListener('error', () => { image.src = basePath + '/assets/images/cover-fallback.svg'; }, { once: true });
      cover.append(image);
      const overlay = document.createElement('span');
      overlay.className = 'prototype-card__overlay';
      const overlayText = document.createElement('span');
      overlayText.append(makeIcon('external'), document.createTextNode(' Découvrir le site'));
      overlay.append(overlayText);
      cover.append(overlay);
      const corner = document.createElement('span');
      corner.className = 'prototype-card__corner';
      corner.append(makeIcon('arrow'));
      cover.append(corner);

      const body = document.createElement('div');
      body.className = 'prototype-card__body';
      const tags = document.createElement('div');
      tags.className = 'tag-row';
      appendText(tags, 'span', 'tag tag--blue', item.category_name);
      appendText(tags, 'span', 'tag tag--subtle', item.domain_name);
      body.append(tags);
      const title = safeLink(item.url, item.name, '', true);
      const heading = document.createElement('h3');
      heading.append(title);
      body.append(heading);
      const description = appendText(body, 'p', '', item.description || 'Une réalisation de notre bibliothèque.');
      if (description.textContent.length > 112) description.textContent = description.textContent.slice(0, 112).trimEnd() + '…';

      const footer = document.createElement('div');
      footer.className = 'prototype-card__footer';
      const date = document.createElement('span');
      date.className = 'prototype-date';
      date.append(makeIcon('clock'));
      const parsedDate = new Date(String(item.created_at || '').replace(' ', 'T'));
      date.append(document.createTextNode(Number.isNaN(parsedDate.getTime()) ? '—' : ' ' + new Intl.DateTimeFormat('fr-FR').format(parsedDate)));
      footer.append(date);
      const visit = safeLink(item.url, 'Voir le site', 'prototype-visit', true);
      visit.append(makeIcon('right'));
      footer.append(visit);
      body.append(footer);

      if (isAdmin) {
        const actions = document.createElement('div');
        actions.className = 'prototype-admin-actions';
        const edit = safeLink(basePath + '/prototypes/' + Number(item.id) + '/edit', 'Modifier', 'prototype-admin-link');
        edit.prepend(makeIcon('edit'));
        actions.append(edit);
        const deleteForm = document.createElement('form');
        deleteForm.method = 'post';
        deleteForm.action = basePath + '/prototypes/' + Number(item.id) + '/delete';
        deleteForm.setAttribute('data-confirm', 'Supprimer définitivement ce prototype ?');
        const csrf = document.createElement('input');
        csrf.type = 'hidden'; csrf.name = '_token'; csrf.value = token;
        const deleteButton = document.createElement('button');
        deleteButton.type = 'submit';
        deleteButton.className = 'prototype-admin-link prototype-admin-link--danger';
        deleteButton.append(makeIcon('trash'), document.createTextNode(' Supprimer'));
        deleteForm.append(csrf, deleteButton);
        deleteForm.addEventListener('submit', (event) => { if (!window.confirm(deleteForm.dataset.confirm)) event.preventDefault(); });
        actions.append(deleteForm);
        body.append(actions);
      }
      card.append(cover, body);
      return card;
    };

    const setPagination = (page, pages) => {
      if (!pagination || !paginationPages) return;
      pagination.hidden = pages <= 1;
      paginationPages.replaceChildren();
      for (let number = Math.max(1, page - 2); number <= Math.min(pages, page + 2); number += 1) {
        const button = document.createElement('button');
        button.type = 'button';
        button.dataset.page = String(number);
        button.className = 'pagination__number' + (number === page ? ' is-current' : '');
        button.textContent = String(number);
        paginationPages.append(button);
      }
      const buttons = $$('[data-page]', pagination);
      buttons.forEach((button) => {
        const targetPage = Number(button.dataset.page);
        if (button.classList.contains('pagination__button')) {
          const isPrevious = button.textContent.includes('Précédent');
          button.disabled = isPrevious ? page <= 1 : page >= pages;
          button.dataset.page = String(isPrevious ? Math.max(1, page - 1) : Math.min(pages, page + 1));
        }
      });
    };

    const updateUrl = (page) => {
      if (!window.history?.replaceState || !form) return;
      const values = new URLSearchParams(new FormData(form));
      if (page > 1) values.set('page', String(page));
      const query = values.toString();
      window.history.replaceState({}, '', form.action + (query ? '?' + query : ''));
    };

    const loadResults = async (page = 1) => {
      if (!form || !grid || !apiUrl) return;
      requestController?.abort();
      requestController = new AbortController();
      const params = new URLSearchParams(new FormData(form));
      params.set('page', String(page));
      library.classList.add('is-loading');
      library.setAttribute('aria-busy', 'true');
      if (skeletons) skeletons.hidden = false;
      try {
        const response = await fetch(apiUrl + '?' + params.toString(), {
          headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
          credentials: 'same-origin',
          signal: requestController.signal,
        });
        if (!response.ok) throw new Error('La recherche est momentanément indisponible.');
        const data = await response.json();
        grid.replaceChildren(...data.items.map(makeCard));
        currentPage = Number(data.page || 1);
        library.dataset.currentPage = String(currentPage);
        if (resultCount) resultCount.textContent = new Intl.NumberFormat('fr-FR').format(data.total || 0);
        if (emptyState) emptyState.hidden = data.items.length > 0;
        setPagination(currentPage, Number(data.pages || 1));
        if (clearButton) {
          const hasFilters = Array.from(new FormData(form).values()).some((value, index) => {
            const key = Array.from(new FormData(form).keys())[index];
            return key === 'q' || key === 'category' || key === 'domain' ? String(value).trim() !== '' : key === 'sort' && value !== 'recent';
          });
          clearButton.hidden = !hasFilters;
        }
        updateUrl(currentPage);
      } catch (error) {
        if (error.name !== 'AbortError') {
          console.error(error);
          if (window.console) console.warn('Prime Sales: recherche instantanée indisponible.');
        }
      } finally {
        library.classList.remove('is-loading');
        library.removeAttribute('aria-busy');
        if (skeletons) skeletons.hidden = true;
      }
    };

    form?.addEventListener('submit', (event) => { event.preventDefault(); loadResults(1); });
    searchInput?.addEventListener('input', () => {
      window.clearTimeout(searchTimer);
      searchTimer = window.setTimeout(() => loadResults(1), 240);
    });
    $$('[data-library-filter]', library).forEach((select) => select.addEventListener('change', () => loadResults(1)));
    pagination?.addEventListener('click', (event) => {
      const button = event.target.closest('[data-page]');
      if (!button || button.disabled) return;
      loadResults(Number(button.dataset.page || 1));
      library.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
    const resetFilters = () => {
      if (searchInput) searchInput.value = '';
      const category = $('[name="category"]', form);
      const domain = $('[name="domain"]', form);
      const sort = $('[name="sort"]', form);
      if (category) category.value = '';
      if (domain) domain.value = '';
      if (sort) sort.value = 'recent';
      loadResults(1);
      searchInput?.focus();
    };
    clearButton?.addEventListener('click', resetFilters);
    $('[data-clear-empty]', library)?.addEventListener('click', resetFilters);
  }
})();
