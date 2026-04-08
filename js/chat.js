const chatForm = document.getElementById('chat-form');
const chatBox = document.getElementById('chat-box');
const messageInput = document.getElementById('message-text');
const sendBtn = document.getElementById('send-btn');
const chatTitle = document.getElementById('chat-title');
const threadsList = document.getElementById('threads-list');
const isAdmin = window.CHAT_IS_ADMIN === true;

let activeUserId = 0;

function escapeHtml(str) {
    const d = document.createElement('div');
    d.textContent = str == null ? '' : String(str);
    return d.innerHTML;
}

function appendMessage(name, text, type) {
    const msgDiv = document.createElement('div');
    msgDiv.classList.add('message', type);
    const author = document.createElement('span');
    author.className = 'author';
    author.textContent = name;
    const body = document.createElement('span');
    body.className = 'msg-body';
    body.innerHTML = escapeHtml(text).replace(/\n/g, '<br>');
    msgDiv.appendChild(author);
    msgDiv.appendChild(body);
    chatBox.appendChild(msgDiv);
    chatBox.scrollTop = chatBox.scrollHeight;
}

function clearChatBox() {
    chatBox.innerHTML = '';
}

function setFormEnabled(on) {
    if (messageInput) messageInput.disabled = !on;
    if (sendBtn) sendBtn.disabled = !on;
}

function messagesUrl() {
    if (isAdmin) {
        return 'vendor/get_messages.php?with=' + encodeURIComponent(String(activeUserId));
    }
    return 'vendor/get_messages.php';
}

async function loadMessages() {
    if (isAdmin && !activeUserId) {
        return;
    }

    const url = messagesUrl();

    let data;
    try {
        const response = await fetch(url, { credentials: 'same-origin' });
        data = await response.json();
    } catch (e) {
        return;
    }

    const messages = data.messages != null ? data.messages : [];
    if (data.error === 'no_admin' && !isAdmin) {
        clearChatBox();
        const ph = document.createElement('div');
        ph.className = 'message system';
        ph.textContent = 'Администратор пока не назначен. Сообщения недоступны.';
        chatBox.appendChild(ph);
        setFormEnabled(false);
        return;
    }

    setFormEnabled(true);
    clearChatBox();
    if (messages.length === 0) {
        const ph = document.createElement('div');
        ph.className = 'message system';
        ph.textContent = 'Пока нет сообщений. Напишите первым.';
        chatBox.appendChild(ph);
        return;
    }

    messages.forEach((msg) => {
        const type = msg.is_my ? 'my' : 'other';
        appendMessage(msg.user_name || '', msg.text || '', type);
    });
}

async function loadThreads() {
    if (!isAdmin || !threadsList) return;
    try {
        const r = await fetch('vendor/get_admin_threads.php', { credentials: 'same-origin' });
        const threads = await r.json();
        threadsList.innerHTML = '';
        if (!Array.isArray(threads) || threads.length === 0) {
            threadsList.innerHTML = '<p class="chat-threads__empty">Нет обращений</p>';
            return;
        }
        threads.forEach((t) => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'chat-thread-item' + (activeUserId === t.user_id ? ' is-active' : '');
            btn.dataset.userId = String(t.user_id);

            const inner = document.createElement('span');
            inner.className = 'chat-thread-item__inner';

            const title = document.createElement('strong');
            title.textContent = t.name || ('#' + t.user_id);

            const previewLabel = document.createElement('span');
            previewLabel.className = 'chat-thread-preview-label';
            previewLabel.textContent = 'Последнее сообщение';

            const preview = document.createElement('span');
            preview.className = 'chat-thread-preview';
            const lt = (t.last_text || '').trim();
            preview.textContent = lt
                ? (lt.length > 72 ? lt.slice(0, 72) + '…' : lt)
                : '—';

            inner.appendChild(title);
            inner.appendChild(previewLabel);
            inner.appendChild(preview);
            btn.appendChild(inner);

            btn.addEventListener('click', () => selectThread(t.user_id, t.name));
            threadsList.appendChild(btn);
        });
    } catch (e) {
        threadsList.innerHTML = '<p class="chat-threads__empty">Ошибка загрузки</p>';
    }
}

function selectThread(userId, displayName) {
    activeUserId = Number(userId);
    if (chatTitle) {
        chatTitle.textContent = displayName ? 'Диалог: ' + displayName : 'Диалог #' + activeUserId;
    }
    document.querySelectorAll('.chat-thread-item').forEach((el) => {
        el.classList.toggle('is-active', Number(el.dataset.userId) === activeUserId);
    });
    setFormEnabled(true);
    loadMessages();
}

chatForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    const text = messageInput.value.trim();
    if (!text) return;
    if (isAdmin && !activeUserId) return;

    const formData = new FormData();
    formData.append('message', text);
    if (isAdmin) {
        formData.append('to_user_id', String(activeUserId));
    }

    messageInput.value = '';

    await fetch('vendor/send_message.php', {
        method: 'POST',
        body: formData,
        credentials: 'same-origin'
    });

    await loadMessages();
    if (isAdmin) {
        await loadThreads();
    }
});

setInterval(() => {
    loadMessages();
}, 2500);

if (isAdmin) {
    (async () => {
        await loadThreads();
        const w = parseInt(new URLSearchParams(window.location.search).get('with') || '0', 10);
        if (w > 0) {
            const item = document.querySelector('.chat-thread-item[data-user-id="' + w + '"]');
            const nm = item && item.querySelector('strong') ? item.querySelector('strong').textContent : ('Пользователь #' + w);
            selectThread(w, nm);
        }
    })();
} else {
    loadMessages();
}
