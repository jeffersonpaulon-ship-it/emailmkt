/**
 * RSVP System Core Javascript Engine
 * Baseado na Fetch API para integrações Ajax assíncronas
 */
declare(strict_types=1); // Tratado mentalmente aqui no JS com Strict Mode
"use strict";

const RsvpApp = {
    
    // Configurações Globais do App
    baseUrl: 'https://wnebr.com/rsvp/',

    /**
     * Importação Assíncrona de Planilhas (CSV) mapeadas do Excel
     * @param {number} eventId 
     * @param {HTMLInputElement} fileInput 
     */
    async importExcel(eventId, fileInput) {
        if (!fileInput.files || fileInput.files.length === 0) {
            this.showNotification('Por favor, selecione um arquivo.', 'error');
            return;
        }

        const formData = new FormData();
        formData.append('event_id', eventId);
        formData.append('excel_file', fileInput.files[0]);

        try {
            this.toggleLoader(true);
            const response = await fetch(`${this.baseUrl}ajax/import-excel.php`, {
                method: 'POST',
                body: formData
            });

            if (!response.ok) throw new Error('Falha de comunicação com o servidor.');

            const result = await response.json();
            
            if (result.success) {
                this.showNotification(result.message, 'success');
                // Recarrega a tabela ou muta o DOM local se necessário
                setTimeout(() => window.location.reload(), 1500);
            } else {
                this.showNotification(result.message, 'error');
            }
        } catch (error) {
            this.showNotification(error.message, 'error');
        } finally {
            this.toggleLoader(false);
        }
    },

    /**
     * Atualização rápida do status do convidado (Atendimentos, Ligações, WhatsApp)
     * @param {number} guestId 
     * @param {string} status ('confirmed', 'declined', 'pending')
     */
    async updateGuestStatus(guestId, status) {
        const formData = new FormData();
        formData.append('guest_id', guestId);
        formData.append('confirmation_status', status);

        try {
            const response = await fetch(`${this.baseUrl}ajax/update-status.php`, {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                this.showNotification('Status atualizado com sucesso!', 'success');
                this.updateDOMBadge(guestId, status);
            } else {
                this.showNotification(result.message, 'error');
            }
        } catch (error) {
            console.error('Erro Ajax:', error);
            this.showNotification('Erro interno de processamento.', 'error');
        }
    },

    /**
     * Dispara um e-mail de teste unitário da campanha ativa
     * @param {number} campaignId 
     * @param {string} email 
     */
    async sendCampaignTest(campaignId, email) {
        if (!email) {
            this.showNotification('Digite um e-mail de destino válido.', 'error');
            return;
        }

        const formData = new FormData();
        formData.append('campaign_id', campaignId);
        formData.append('test_email', email);

        try {
            const response = await fetch(`${this.baseUrl}ajax/send-campaign.php?action=test`, {
                method: 'POST',
                body: formData
            });

            const result = await response.json();
            if (result.success) {
                this.showNotification(result.message, 'success');
            } else {
                this.showNotification(result.message, 'error');
            }
        } catch (error) {
            this.showNotification('Erro ao disparar teste.', 'error');
        }
    },

    /**
     * Helpers Visuais de Interface
     */
    updateDOMBadge(guestId, status) {
        const badge = document.querySelector(`#badge-guest-${guestId}`);
        if (badge) {
            badge.className = `badge badge-${status}`;
            badge.textContent = status === 'confirmed' ? 'Confirmado' : (status === 'declined' ? 'Recusado' : 'Pendente');
        }
    },

    showNotification(message, type = 'success') {
        // Implementação simplificada de feedback. Pode ser substituída por Toasts customizados.
        alert(`[${type.toUpperCase()}] ${message}`);
    },

    toggleLoader(show) {
        // Encontre ou injete uma camada de loading no DOM
        const loader = document.getElementById('global-loader');
        if (loader) {
            loader.style.display = show ? 'flex' : 'none';
        }
    }
};