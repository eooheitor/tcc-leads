window.gridData = function () {
    return {
        // estado do modal
        showModal: false,
        loading: false,
        editingId: null,
        modalTitle: "",

        // form dinâmico (renderizado no backend)
        formFieldsHtml: "",
        formMethod: "POST",
        formAction: "",
        formEnctype: "application/x-www-form-urlencoded",

        // injetados pelo blade
        csrfToken: "",
        storeUrl: "",
        editBaseUrl: "",
        deleteBaseUrl: "",
        modelName: "",
        formCreateUrl: "",
        formEditBaseUrl: "",

        init() {
            // BIND para manter o this correto nos listeners
            this._moneyInputHandler = (e) => this.onMoneyInput(e);
            this._moneyBlurHandler = (e) => this.onMoneyBlur(e);
            this._phoneInputHandler = (e) => this.onPhoneInput(e);
            this._phoneBlurHandler = (e) => this.onPhoneBlur(e);

            document.addEventListener('input', this._moneyInputHandler, true);
            document.addEventListener('blur', this._moneyBlurHandler, true);
            document.addEventListener('input', this._phoneInputHandler, true);
            document.addEventListener('blur', this._phoneBlurHandler, true);
        },

        destroy() {
            document.removeEventListener('input', this._moneyInputHandler, true);
            document.removeEventListener('blur', this._moneyBlurHandler, true);
            document.removeEventListener('input', this._phoneInputHandler, true);
            document.removeEventListener('blur', this._phoneBlurHandler, true);
        },

        onMoneyInput(e) {
            const el = e.target;
            if (el.disabled) return;
            if (!el.matches('.money-mask, input[name$="_budget"], input[name="bid_amount"]')) return;
            let digits = el.value.replace(/\D/g, '');
            digits = digits.replace(/^0+/, '');
            if (digits.length === 0) digits = '0';
            if (digits.length === 1) digits = '0' + digits;

            const inteiros = digits.slice(0, -2) || '0';
            const centavos = digits.slice(-2);
            const inteirosFmt = inteiros.replace(/\B(?=(\d{3})+(?!\d))/g, '.');

            el.value = `${inteirosFmt},${centavos}`;
        },
        onMoneyBlur(e) {
            const el = e.target;
            if (el.disabled) return;
            this.onMoneyInput(e);
        },

        getCsrfToken() {
            if (this.csrfToken) return this.csrfToken;
            const metaTag = document.querySelector('meta[name="csrf-token"]');
            if (metaTag) return metaTag.getAttribute('content');
            const hidden = document.querySelector('input[name="_token"]');
            if (hidden) return hidden.value;
            console.error('Token CSRF não encontrado!');
            return null;
        },

        openModal(id = null) {
            this.showModal = true;
            this.editingId = id || null;
            this.loading = true;

            const url = id
                ? `${this.formEditBaseUrl}/${id}/edit`
                : this.formCreateUrl;

            fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(r => r.json())
                .then(res => {
                    if (!res.success) throw new Error(res.message || 'Falha ao carregar formulário.');
                    this.modalTitle = res.title || (id ? 'Editar' : 'Novo');
                    this.formMethod = res.method || (id ? 'PUT' : 'POST');
                    this.formAction = res.action || (id ? `${this.editBaseUrl}/${id}` : this.storeUrl);
                    this.formEnctype = res.multipart ? 'multipart/form-data' : 'application/x-www-form-urlencoded';
                    // atenção: backend retorna "fields_html" (apenas os campos)
                    this.formFieldsHtml = res.fields_html || res.html || '';
                })
                .catch(err => {
                    console.error(err);
                    alert('Erro ao carregar o formulário.');
                    this.closeModal();
                })
                .finally(() => { this.loading = false; });
        },

        async handleFormSubmit() {
            this.loading = true;
            try {
                // seleciona o <form> do modal atual
                const modal = document.querySelector('.fixed.inset-0.z-50');
                const form = modal ? modal.querySelector('form') : document.querySelector('form');
                if (!form) throw new Error('Form não encontrado.');

                const formData = new FormData(form);

                // csrf
                const token = this.getCsrfToken();
                if (token && !formData.has('_token')) formData.append('_token', token);

                // métodos REST via POST + _method
                if (['PUT', 'PATCH', 'DELETE'].includes(this.formMethod)) {
                    formData.append('_method', this.formMethod);
                }

                const resp = await fetch(this.formAction, {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body: formData,
                });

                const raw = await resp.text();
                let data;
                try { data = JSON.parse(raw); } catch { throw new Error('Falha ao processar resposta do servidor.'); }

                if (resp.ok && data?.success) {
                    alert(data.message || 'Salvo com sucesso.');
                    this.closeModal();
                    window.location.reload();
                    return;
                }

                if (resp.status === 422 && data?.errors) {
                    const flat = Object.values(data.errors).flat().join('\n');
                    throw new Error(flat || (data.message || 'Dados inválidos.'));
                }

                throw new Error(data?.message || 'Erro ao salvar.');
            } catch (e) {
                console.error('Salvar falhou:', e);
                alert(`Erro ao salvar: ${e.message}`);
            } finally {
                this.loading = false;
            }
        },

        async deleteItem(id) {
            if (!confirm(`Tem certeza que deseja excluir este ${this.modelName}?`)) return;

            try {
                const token = this.getCsrfToken();
                if (!token) throw new Error('Token CSRF não encontrado');

                const resp = await fetch(`${this.deleteBaseUrl}/${id}`, {
                    method: 'POST', // garante via POST + _method=DELETE para consistência
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': token },
                    body: new URLSearchParams({ _method: 'DELETE' }),
                });

                const data = await resp.json();
                if (resp.ok && data?.success) {
                    alert(data.message || 'Excluído com sucesso.');
                    window.location.reload();
                } else {
                    throw new Error(data?.message || 'Erro ao excluir');
                }
            } catch (e) {
                console.error('Erro ao excluir:', e);
                alert(`Erro ao excluir: ${e.message}`);
            }
        },

        closeModal() {
            this.showModal = false;
            this.loading = false;
            this.editingId = null;
            this.modalTitle = '';
            this.formMethod = 'POST';
            this.formAction = '';
            this.formEnctype = 'application/x-www-form-urlencoded';
            this.formFieldsHtml = '';
        },

        onPhoneInput(e) {
            const el = e.target;
            if (el.disabled) return;
            if (!el.classList || !el.classList.contains('phone-mask')) return;

            // permite '+' só se for no início
            let v = el.value;
            const hasPlus = v.trim().startsWith('+');
            // remove tudo que não for dígito
            v = v.replace(/\D+/g, '');
            // reanexa +55 se tinha + e começou com 55
            if (hasPlus && v.startsWith('55')) {
                v = '+55' + v.slice(2);
            }
            el.value = this.formatPhone(v);
        },

        onPhoneBlur(e) {
            const el = e.target;
            if (el.disabled) return;
            if (!el.classList || !el.classList.contains('phone-mask')) return;
            el.value = this.formatPhone(el.value);
        },

        /**
         * Aceita:
         *  - "5547999999999"  -> "+55 (47) 99999-9999"
         *  - "47999999999"    -> "(47) 99999-9999"
         *  - "4734567890"     -> "(47) 3456-7890"
         *  - "+55 47 999..."  -> "+55 (47) 999..."
         */
        formatPhone(raw) {
            if (!raw) return '';

            // normaliza: mantém somente '+' inicial (se houver) e dígitos
            const startsPlus = String(raw).trim().startsWith('+');
            let digits = String(raw).replace(/\D+/g, '');

            let hasCountry = false;
            if (startsPlus && digits.startsWith('55')) {
                hasCountry = true;
                digits = digits.slice(2); // remove 55 para formatar DDD+numero
            }

            const ddd = digits.slice(0, 2);
            let rest = digits.slice(2);

            if (ddd.length === 0) {
                return hasCountry ? '+55 ' : '';
            }
            if (ddd.length < 2) {
                return (hasCountry ? '+55 ' : '') + `(${ddd}`;
            }

            // limita o restante em 9 dígitos no máx
            if (rest.length > 9) rest = rest.slice(0, 9);

            // 9 dígitos => 5-4 (celular), senão 4-4 (fixo ou incompleto)
            const isNine = rest.length >= 9;
            const left = isNine ? rest.slice(0, 5) : rest.slice(0, 4);
            const right = rest.slice(isNine ? 5 : 4);

            const num = right ? `${left}-${right}` : left;

            return (hasCountry ? '+55 ' : '') + `(${ddd}) ${num}`;
        },
    };
};
