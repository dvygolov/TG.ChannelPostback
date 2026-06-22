// Global state
let bots = [];
let channels = [];

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    loadStats();
    loadBots();
    loadChannels();
    
    // Reload data every 30 seconds
    setInterval(loadStats, 30000);
});

// Tab switching
function switchTab(tabName) {
    // Hide all tabs
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.remove('active');
    });
    
    // Remove active from buttons
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Show selected tab
    document.getElementById('tab-' + tabName).classList.add('active');
    event.target.classList.add('active');
}

// Load statistics
async function loadStats() {
    try {
        const response = await fetch('api.php?action=get_stats');
        const data = await response.json();
        
        if (data.success) {
            document.getElementById('stat-total').textContent = data.data.total || 0;
            document.getElementById('stat-pending').textContent = data.data.pending || 0;
            document.getElementById('stat-subscribed').textContent = data.data.subscribed || 0;
        }
    } catch (error) {
        console.error('Failed to load stats:', error);
    }
}

// ===== BOTS =====
async function loadBots() {
    try {
        const response = await fetch('api.php?action=get_bots');
        const data = await response.json();
        
        if (data.success) {
            bots = data.data;
            renderBots();
            updateBotSelect();
        }
    } catch (error) {
        console.error('Failed to load bots:', error);
        showError('Ошибка загрузки ботов');
    }
}

function renderBots() {
    const tbody = document.getElementById('bots-tbody');
    
    if (bots.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="loading">Нет ботов. Добавьте первого!</td></tr>';
        return;
    }
    
    tbody.innerHTML = bots.map(bot => `
        <tr>
            <td>${bot.id}</td>
            <td><strong>${escapeHtml(bot.name)}</strong></td>
            <td>
                <span class="status-badge ${bot.is_active ? 'status-active' : 'status-inactive'}">
                    ${bot.is_active ? 'Активен' : 'Неактивен'}
                </span>
            </td>
            <td>${formatDate(bot.created_at)}</td>
            <td>
                <button class="btn-icon btn-warning" 
                        onclick="showEditBotModal(${bot.id})"
                        title="Редактировать">
                    <span class="icon">✏️</span>
                </button>
                <button class="btn-icon ${bot.is_active ? 'btn-secondary' : 'btn-success'}" 
                        onclick="toggleBot(${bot.id})"
                        title="${bot.is_active ? 'Деактивировать' : 'Активировать'}">
                    <span class="icon">${bot.is_active ? '⏸' : '▶'}</span>
                </button>
                <button class="btn-icon btn-danger" 
                        onclick="deleteBot(${bot.id}, '${escapeHtml(bot.name)}')"
                        title="Удалить">
                    <span class="icon">🗑</span>
                </button>
            </td>
        </tr>
    `).join('');
}

function showAddBotModal() {
    document.getElementById('modal-add-bot').classList.add('show');
}

async function addBot(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    
    try {
        const response = await fetch('api.php?action=add_bot', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showSuccess('Бот успешно добавлен!');
            closeModal('modal-add-bot');
            form.reset();
            loadBots();
        } else {
            showError(data.error || 'Ошибка при добавлении бота');
        }
    } catch (error) {
        showError('Ошибка при добавлении бота');
    }
}

async function toggleBot(id) {
    if (!confirm('Изменить статус бота?')) return;
    
    const formData = new FormData();
    formData.append('id', id);
    
    try {
        const response = await fetch('api.php?action=toggle_bot', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showSuccess('Статус бота обновлён');
            loadBots();
        } else {
            showError(data.error || 'Ошибка');
        }
    } catch (error) {
        showError('Ошибка при обновлении');
    }
}

async function deleteBot(id, name) {
    if (!confirm(`Удалить бота "${name}"?\n\nБудут удалены все связанные каналы и инвайты!`)) return;
    
    const formData = new FormData();
    formData.append('id', id);
    
    try {
        const response = await fetch('api.php?action=delete_bot', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showSuccess('Бот удалён');
            loadBots();
            loadChannels();
        } else {
            showError(data.error || 'Ошибка при удалении');
        }
    } catch (error) {
        showError('Ошибка при удалении');
    }
}

function showEditBotModal(id) {
    const bot = bots.find(b => b.id === id);
    if (!bot) return;
    
    document.getElementById('edit-bot-id').value = bot.id;
    document.getElementById('edit-bot-name').value = bot.name;
    document.getElementById('edit-bot-token').value = bot.token;
    
    document.getElementById('modal-edit-bot').classList.add('show');
}

async function updateBot(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    
    try {
        const response = await fetch('api.php?action=update_bot', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showSuccess('Бот успешно обновлён!');
            closeModal('modal-edit-bot');
            form.reset();
            loadBots();
        } else {
            showError(data.error || 'Ошибка при обновлении бота');
        }
    } catch (error) {
        showError('Ошибка при обновлении бота');
    }
}

// ===== CHANNELS =====
async function loadChannels() {
    try {
        const response = await fetch('api.php?action=get_channels');
        const data = await response.json();
        
        if (data.success) {
            channels = data.data;
            renderChannels();
        }
    } catch (error) {
        console.error('Failed to load channels:', error);
        showError('Ошибка загрузки каналов');
    }
}

function renderChannels() {
    const tbody = document.getElementById('channels-tbody');
    
    if (channels.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="loading">Нет каналов. Добавьте первый!</td></tr>';
        return;
    }
    
    tbody.innerHTML = channels.map(channel => {
        const bot = bots.find(b => b.id === channel.bot_id);
        const botName = bot ? bot.name : 'Unknown';
        
        return `
            <tr>
                <td>${channel.id}</td>
                <td>
                    <strong>${escapeHtml(channel.channel_name || channel.channel_id)}</strong>
                    <br>
                    <small>${escapeHtml(channel.channel_id)}</small>
                </td>
                <td>${escapeHtml(botName)}</td>
                <td>
                    <span class="status-badge ${channel.is_active ? 'status-active' : 'status-inactive'}">
                        ${channel.is_active ? 'Активен' : 'Неактивен'}
                    </span>
                </td>
                <td>
                    <button class="btn-icon btn-primary" 
                            onclick="copySubscribeLink('${escapeHtml(channel.channel_id)}')"
                            title="Скопировать ссылку для лендинга">
                        <span class="icon">📋</span>
                    </button>
                    <button class="btn-icon btn-warning" 
                            onclick="showEditChannelModal(${channel.id})"
                            title="Редактировать">
                        <span class="icon">✏️</span>
                    </button>
                    <button class="btn-icon ${channel.is_active ? 'btn-secondary' : 'btn-success'}" 
                            onclick="toggleChannel(${channel.id})"
                            title="${channel.is_active ? 'Деактивировать' : 'Активировать'}">
                        <span class="icon">${channel.is_active ? '⏸' : '▶'}</span>
                    </button>
                    <button class="btn-icon btn-danger" 
                            onclick="deleteChannel(${channel.id})"
                            title="Удалить">
                        <span class="icon">🗑</span>
                    </button>
                </td>
            </tr>
        `;
    }).join('');
}

function showAddChannelModal() {
    if (bots.length === 0) {
        showError('Сначала добавьте хотя бы одного бота!');
        return;
    }
    
    document.getElementById('modal-add-channel').classList.add('show');
}

function updateBotSelect() {
    const select = document.getElementById('channel-bot-select');
    
    select.innerHTML = '<option value="">-- Выберите бота --</option>' +
        bots.filter(b => b.is_active).map(bot => 
            `<option value="${bot.id}">${escapeHtml(bot.name)}</option>`
        ).join('');
}

async function addChannel(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    
    const submitBtn = form.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.textContent = 'Проверка...';
    
    try {
        const response = await fetch('api.php?action=add_channel', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showSuccess('Канал успешно добавлен!');
            closeModal('modal-add-channel');
            form.reset();
            loadChannels();
        } else {
            showError(data.error || 'Ошибка при добавлении канала');
        }
    } catch (error) {
        showError('Ошибка при добавлении канала');
    } finally {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Добавить';
    }
}

async function toggleChannel(id) {
    if (!confirm('Изменить статус канала?')) return;
    
    const formData = new FormData();
    formData.append('id', id);
    
    try {
        const response = await fetch('api.php?action=toggle_channel', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showSuccess('Статус канала обновлён');
            loadChannels();
        } else {
            showError(data.error || 'Ошибка');
        }
    } catch (error) {
        showError('Ошибка при обновлении');
    }
}

async function deleteChannel(id) {
    if (!confirm('Удалить канал?\n\nБудут удалены все связанные инвайты!')) return;
    
    const formData = new FormData();
    formData.append('id', id);
    
    try {
        const response = await fetch('api.php?action=delete_channel', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showSuccess('Канал удалён');
            loadChannels();
        } else {
            showError(data.error || 'Ошибка при удалении');
        }
    } catch (error) {
        showError('Ошибка при удалении');
    }
}

function showEditChannelModal(id) {
    const channel = channels.find(c => c.id === id);
    if (!channel) return;
    
    document.getElementById('edit-channel-id').value = channel.id;
    document.getElementById('edit-channel-channel-id').value = channel.channel_id;
    document.getElementById('edit-channel-name').value = channel.channel_name || '';
    document.getElementById('edit-channel-postback').value = channel.postback_url;
    
    document.getElementById('modal-edit-channel').classList.add('show');
}

async function updateChannel(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    
    try {
        const response = await fetch('api.php?action=update_channel', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showSuccess('Канал успешно обновлён!');
            closeModal('modal-edit-channel');
            form.reset();
            loadChannels();
        } else {
            showError(data.error || 'Ошибка при обновлении канала');
        }
    } catch (error) {
        showError('Ошибка при обновлении канала');
    }
}

// ===== MODAL =====
function closeModal(modalId) {
    document.getElementById(modalId).classList.remove('show');
}

// Close modal on outside click
window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.classList.remove('show');
    }
}

// ===== UTILITIES =====
function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleString('ru-RU');
}

function showSuccess(message) {
    alert('✅ ' + message);
}

function showError(message) {
    alert('❌ ' + message);
}

// Copy subscribe link for landing page
function copySubscribeLink(channelId) {
    // Get current domain or use placeholder
    const baseUrl = window.location.origin;
    const subscribeUrl = `${baseUrl}/subscribe.php?channel=${encodeURIComponent(channelId)}&clickid={clickid}`;
    
    // Copy to clipboard
    navigator.clipboard.writeText(subscribeUrl).then(() => {
        // Show success tooltip
        showSuccess('Ссылка скопирована в буфер обмена!\n\n' + subscribeUrl);
    }).catch(err => {
        // Fallback for older browsers
        const textarea = document.createElement('textarea');
        textarea.value = subscribeUrl;
        textarea.style.position = 'fixed';
        textarea.style.opacity = '0';
        document.body.appendChild(textarea);
        textarea.select();
        try {
            document.execCommand('copy');
            showSuccess('Ссылка скопирована в буфер обмена!\n\n' + subscribeUrl);
        } catch (e) {
            showError('Не удалось скопировать ссылку');
        }
        document.body.removeChild(textarea);
    });
}
