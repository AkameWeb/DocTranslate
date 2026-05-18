// ==================== История AI-генераций ====================
let aiHistoryOffset = 0;
let isLoadingAiHistory = false;
let hasMoreAiHistory = true;

async function loadAiHistory(reset = false) {
    if (reset) {
        aiHistoryOffset = 0;
        hasMoreAiHistory = true;
        document.getElementById('aiHistoryList').innerHTML = '';
    }
    if (isLoadingAiHistory || !hasMoreAiHistory) return;
    
    isLoadingAiHistory = true;
    try {
        const response = await fetch(`api/ai/get-history.php?offset=${aiHistoryOffset}`);
        const data = await response.json();
        
        if (data.length === 0) {
            hasMoreAiHistory = false;
            if (aiHistoryOffset === 0) {
                document.getElementById('aiHistoryList').innerHTML = '<p style="text-align:center;color:var(--text-muted);padding:20px;">📭 История генераций пуста</p>';
            }
        } else {
            renderAiHistory(data);
            aiHistoryOffset += data.length;
        }
    } catch (err) {
        console.error('Failed to load AI history', err);
    } finally {
        isLoadingAiHistory = false;
    }
}

function renderAiHistory(items) {
    const container = document.getElementById('aiHistoryList');
    if (!container) return;
    
    const html = items.map(item => `
        <div class="conversion-item" data-id="${item.id}">
            <div class="conversion-info">
                <div class="conversion-name">📝 ${escapeHtml(item.prompt)}</div>
                <div class="conversion-meta">
                    <span> ${item.model}</span>
                    <span>📅 ${item.created_at}</span>
                </div>
                <div class="conversion-preview">${escapeHtml(item.response)}</div>
            </div>
            <div style="display: flex; gap: 8px;">
                <button class="copy-ai-result-btn" data-id="${item.id}" data-result="${escapeAttr(item.response)}">📋 Копировать</button>
                <button class="delete-ai-generation-btn" data-id="${item.id}" title="Удалить"><i class="far fa-trash-alt"></i></button>
            </div>
        </div>
    `).join('');
    
    container.insertAdjacentHTML('beforeend', html);
    
    // Копирование результата
    document.querySelectorAll('.copy-ai-result-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const result = btn.dataset.result;
            navigator.clipboard.writeText(result).then(() => showToast('✅ Результат скопирован'));
        });
    });
    
    // Удаление записи
    document.querySelectorAll('.delete-ai-generation-btn').forEach(btn => {
        btn.addEventListener('click', async (e) => {
            e.stopPropagation();
            const id = btn.dataset.id;
            if (confirm('Удалить эту запись?')) {
                await fetch('api/ai/delete-generation.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id })
                });
                loadAiHistory(true);
                showToast('✅ Запись удалена');
            }
        });
    });
}

async function clearAllAiHistory() {
    if (!confirm('⚠️ Удалить всю историю генераций?')) return;
    await fetch('api/ai/clear-history.php');
    loadAiHistory(true);
    showToast('✅ История очищена');
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function escapeAttr(text) {
    return text.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

// Добавляем обработчик для кнопки "Очистить всё"
function initAiHistory() {
    const clearBtn = document.getElementById('clearAiHistoryBtn');
    if (clearBtn) {
        clearBtn.addEventListener('click', clearAllAiHistory);
    }
    loadAiHistory();
}

// Запускаем после загрузки страницы (если блок генератора активен)
if (document.getElementById('ai-generator-block')) {
    document.addEventListener('DOMContentLoaded', () => {
        initAiHistory();
    });
}