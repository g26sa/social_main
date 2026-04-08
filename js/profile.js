function escapeHtml(str) {
    if (str == null) return '';
    const d = document.createElement('div');
    d.textContent = String(str);
    return d.innerHTML;
}

function escapeAttr(str) {
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;')
        .replace(/</g, '&lt;');
}

function formatPostBody(text) {
    const raw = text != null ? String(text) : '';
    return escapeHtml(raw).replace(/\r\n/g, '\n').replace(/\n/g, '<br>');
}

// Функция загрузки постов
async function loadPosts() {
    const container = document.getElementById('posts-container');
    if (!container) return;
    container.innerHTML = '<p style="text-align:center;color:#FFC000;">Загрузка…</p>';

    let posts;
    try {
        const response = await fetch('vendor/get_posts.php', { credentials: 'same-origin' });
        const raw = await response.text();
        posts = JSON.parse(raw);
    } catch (e) {
        container.innerHTML = '<p style="text-align:center;color:#f88;">Не удалось загрузить посты. Обновите страницу.</p>';
        return;
    }

    container.innerHTML = '';

    if (!Array.isArray(posts) || posts.length === 0) {
        container.innerHTML = '<p style="text-align:center;color:#FFC000;font-weight:bold;">У вас ещё нет постов.</p>';
        return;
    }

    posts.forEach(post => {
        const titleRaw = post.title != null ? String(post.title).trim() : '';
        const titleHtml = titleRaw !== '' ? escapeHtml(titleRaw) : 'Без заголовка';

        const bodyRaw = post.text != null ? post.text : (post.content != null ? post.content : '');
        const bodyHtml = formatPostBody(bodyRaw);

        const imgVal = post.image != null ? String(post.image).trim() : '';
        const imgBlock = imgVal !== ''
            ? `<img src="${escapeAttr(imgVal)}" alt="">`
            : '';
        const imgClass = imgVal !== ''
            ? 'post-img-container'
            : 'post-img-container post-img-container--empty';

        const cnt = post.comments_count != null ? post.comments_count : 0;
        const pid = Number(post.id);

        const postHtml = `
            <div class="post" data-id="${pid}">
                <div class="${imgClass}">${imgBlock}</div>
                <div class="post-content">
                    <div class="post-card-title">${titleHtml}</div>
                    <div class="post-text">${bodyHtml}</div>
                    <div class="post-footer">
                        <span class="post-date">${escapeHtml(post.date != null ? post.date : '')}</span>
                        <button type="button" class="comment-btn" onclick="toggleComments(${pid})">
                            Комментарии (${cnt})
                        </button>
                    </div>
                    <div class="comments-section" id="comments-${pid}" style="display:none" data-loaded="">
                        <div class="comments-list"></div>
                        <form onsubmit="sendComment(event, ${pid})">
                            <input type="text" name="comment" placeholder="Напишите комментарий..." required>
                            <button type="submit">&gt;</button>
                        </form>
                    </div>
                </div>
            </div>
        `;
        container.innerHTML += postHtml;
    });
}

// Отправка нового поста (только на странице, где есть форма)
const postForm = document.getElementById('post-form');
if (postForm) {
    postForm.onsubmit = async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        await fetch('vendor/create_post.php', { method: 'POST', body: formData, credentials: 'same-origin' });
        e.target.reset();
        loadPosts();
    };
}

// Функция для комментариев
async function sendComment(e, postId) {
    e.preventDefault();
    const input = e.target.querySelector('input[name="comment"], input[type="text"]');
    if (!input) return;
    const formData = new FormData();
    formData.append('post_id', String(postId));
    formData.append('text', input.value);

    await fetch('vendor/add_comment.php', { method: 'POST', body: formData, credentials: 'same-origin' });

    await loadPosts();
    const reopened = document.getElementById(`comments-${postId}`);
    if (reopened) {
        reopened.style.display = 'block';
        await loadCommentsIntoSection(postId);
    }
}

async function loadCommentsIntoSection(postId) {
    const section = document.getElementById(`comments-${postId}`);
    if (!section) return;
    const list = section.querySelector('.comments-list');
    if (!list) return;

    list.innerHTML = '<p class="comments-hint">Загрузка…</p>';
    try {
        const r = await fetch('vendor/get_comments.php?post_id=' + encodeURIComponent(postId), { credentials: 'same-origin' });
        const raw = await r.text();
        const items = JSON.parse(raw);
        if (!Array.isArray(items) || items.length === 0) {
            list.innerHTML = '<p class="comments-hint">Комментариев пока нет.</p>';
        } else {
            list.innerHTML = items.map((c) => {
                const a = escapeHtml(c.author != null ? c.author : '');
                const b = formatPostBody(c.body != null ? c.body : '');
                const d = escapeHtml(c.created != null ? c.created : '');
                return `<div class="comment-item"><span class="comment-author">${a}</span><span class="comment-meta">${d}</span><div class="comment-body">${b}</div></div>`;
            }).join('');
        }
        section.dataset.loaded = '1';
    } catch (err) {
        list.innerHTML = '<p class="comments-hint">Не удалось загрузить комментарии.</p>';
    }
}

async function toggleComments(postId) {
    const section = document.getElementById(`comments-${postId}`);
    if (!section) return;

    const hidden = section.style.display === 'none' || section.style.display === '';
    if (!hidden) {
        section.style.display = 'none';
        return;
    }

    section.style.display = 'block';
    if (section.dataset.loaded !== '1') {
        await loadCommentsIntoSection(postId);
    }
}

// Кнопка "Редактировать" в профиле
const editBtn = document.getElementById('edit-profile');
if (editBtn) {
    editBtn.addEventListener('click', () => {
        window.location.href = 'editer_profile.php';
    });
}

// Панель управления профилем
const updatePictureBtn = document.getElementById('update-picture');
const deletePictureBtn = document.getElementById('delete-picture');
const createPostBtn = document.getElementById('create-post-btn');

const avatarInput = document.getElementById('avatar-input');
const profilePicture = document.getElementById('profile-picture');
const createPostSection = document.getElementById('create-post');

if (updatePictureBtn && avatarInput) {
    updatePictureBtn.addEventListener('click', () => avatarInput.click());
}

if (deletePictureBtn && profilePicture) {
    deletePictureBtn.addEventListener('click', async () => {
        const res = await fetch('delete_avatar.php', { method: 'POST', credentials: 'same-origin' });
        let data;
        try {
            data = await res.json();
        } catch (e) {
            return;
        }
        if (data && data.success && data.path) {
            profilePicture.src = data.path;
        }
    });
}

if (createPostBtn && createPostSection) {
    createPostBtn.addEventListener('click', () => {
        createPostSection.style.display = 'block';
        const textarea = document.getElementById('post-text');
        if (textarea) textarea.focus();
        createPostSection.scrollIntoView({ behavior: 'smooth', block: 'center' });
    });
}

// Загрузка аватара по клику на иконку
if (profilePicture && avatarInput) {
    profilePicture.style.cursor = 'pointer';

    profilePicture.addEventListener('click', () => {
        avatarInput.click();
    });

    avatarInput.addEventListener('change', async () => {
        if (!avatarInput.files || avatarInput.files.length === 0) return;

        const file = avatarInput.files[0];
        const formData = new FormData();
        formData.append('avatar', file);

        const res = await fetch('upload_avatar.php', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        });

        let data;
        try {
            data = await res.json();
        } catch (e) {
            return;
        }

        if (data && data.success && data.path) {
            profilePicture.src = data.path;
        }
    });
}

loadPosts();
