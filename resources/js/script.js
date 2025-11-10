window.gridData = function () {
    return {

        // ------------------ ESTADO GERAL ------------------
        showModal: false,
        loading: false,
        editingId: null,
        modalTitle: "",
        formMethod: "POST",
        formAction: "",
        formEnctype: "application/x-www-form-urlencoded",
        formFieldsHtml: "",

        // ------------------ TABS (usamos nomes compatíveis com o Blade) ------------------
        // no Blade, em adsets_table.blade, usamos 'conjunto' e 'anuncios'
        activeTab: 'conjunto',   // 'conjunto' | 'anuncios'
        adsEnabled: false,

        // HTML dinâmico para aba de anúncios
        adsFormHtml: '',
        adsGridHtml: '',

        // ------------------ INJETADOS PELO BLADE ------------------
        csrfToken: "",
        storeUrl: "",
        editBaseUrl: "",
        deleteBaseUrl: "",
        formCreateUrl: "",
        formEditBaseUrl: "",
        modelName: "",

        // para anúncios
        adsStoreUrl: '',      // deve vir do Blade: route('anuncios.store')
        currentAdSetId: null,
        currentAdId: null,
        adsEditBaseUrl: '',
        adsUpdateBaseUrl: '',
        deletingAdId: null,

        // ------------------ INIT / DESTROY (máscaras) ------------------
        init() {
            // BINDs das máscaras
            this._moneyInputHandler = (e) => this.onMoneyInput(e);
            this._moneyBlurHandler = (e) => this.onMoneyBlur(e);
            this._phoneInputHandler = (e) => this.onPhoneInput(e);
            this._phoneBlurHandler = (e) => this.onPhoneBlur(e);

            document.addEventListener('input', this._moneyInputHandler, true);
            document.addEventListener('blur', this._moneyBlurHandler, true);
            document.addEventListener('input', this._phoneInputHandler, true);
            document.addEventListener('blur', this._phoneBlurHandler, true);

            // 🔥 delegação GLOBAL para os botões de editar/excluir anúncio
            this._adsGridClickHandler = (e) => {
                const editBtn = e.target.closest('[data-ad-edit-id]');
                const delBtn = e.target.closest('[data-ad-delete-id]');

                if (editBtn) {
                    e.preventDefault();
                    const adId = editBtn.getAttribute('data-ad-edit-id');
                    if (adId) {
                        this.openAdForEdit(adId);
                    }
                    return;
                }

                if (delBtn) {
                    const adId = delBtn.getAttribute('data-ad-delete-id');
                    console.log('Clique em excluir anúncio:', adId);
                    this.deleteAd(adId);
                    return;
                }
            };

            document.addEventListener('click', this._adsGridClickHandler, true);
        },

        destroy() {
            document.removeEventListener('input', this._moneyInputHandler, true);
            document.removeEventListener('blur', this._moneyBlurHandler, true);
            document.removeEventListener('input', this._phoneInputHandler, true);
            document.removeEventListener('blur', this._phoneBlurHandler, true);
            document.removeEventListener('click', this._adsGridClickHandler, true);

            if (this._adsGridClickHandler) {
                document.removeEventListener('click', this._adsGridClickHandler, true);
            }
        },

        // ------------------ MÁSCARA MONEY ------------------
        onMoneyInput(e) {
            const el = e.target;
            if (el.disabled) return;
            if (!el.matches('.money-mask, input[name$="_budget"], input[name="bid_amount"]')) return;

            if (el.value.replace(/\D/g, '') === '') {
                el.value = '';
                return;
            }

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

        // ------------------ CSRF ------------------
        getCsrfToken() {
            if (this.csrfToken) return this.csrfToken;

            const metaTag = document.querySelector('meta[name="csrf-token"]');
            if (metaTag) return metaTag.getAttribute('content');

            const hidden = document.querySelector('input[name="_token"]');
            if (hidden) return hidden.value;

            console.error('Token CSRF não encontrado!');
            return null;
        },

        async openAdForEdit(adId) {
            if (!this.adsEditBaseUrl) {
                console.error('adsEditBaseUrl não definido');
                alert('Endpoint de edição de anúncio não configurado.');
                return;
            }

            try {
                this.loading = true;

                const resp = await fetch(`${this.adsEditBaseUrl}/${adId}/edit`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                });

                const data = await resp.json();

                if (!resp.ok || !data.success) {
                    throw new Error(data.message || 'Erro ao carregar anúncio.');
                }

                const anuncio = data.anuncio || {};
                this.currentAdId = anuncio.id || adId;

                // força aba de anúncios
                this.activeTab = 'anuncios';

                // espera o Alpine re-renderizar a aba
                await this.$nextTick();

                const adsForm = document.getElementById('adsetAdsForm');
                if (!adsForm) {
                    console.warn('⚠️ Form de anúncios (adsetAdsForm) não encontrado no DOM (mesmo após nextTick).');
                    return;
                }

                const setVal = (name, value) => {
                    const el = adsForm.querySelector(`[name="${name}"]`);
                    if (!el) {
                        console.warn(`Campo [name="${name}"] não encontrado no formulário de anúncios.`);
                        return;
                    }
                    el.value = value ?? '';
                };

                // básicos
                setVal('ad_name', anuncio.name || '');
                setVal('status', anuncio.status || 'PAUSED');

                // campos do creative / link_data se vierem da API
                const creative = anuncio.creative || {};
                const linkData = creative.object_story_spec?.link_data || {};

                setVal('link_url', linkData.link || '');
                setVal('headline', linkData.name || '');
                setVal('description', linkData.description || '');
                setVal('call_to_action', linkData.call_to_action?.type || '');

                // Trix (primary_text)
                const msg = linkData.message || '';
                const primaryHidden = adsForm.querySelector('input[name="primary_text"]');
                if (primaryHidden) primaryHidden.value = msg;

                const trixEditor = adsForm.querySelector('trix-editor[input="primary_text"]');
                if (trixEditor && trixEditor.editor) {
                    trixEditor.editor.loadHTML(msg);
                }

            } catch (e) {
                console.error('Erro ao carregar anúncio para edição:', e);
                alert('Erro ao carregar anúncio para edição: ' + e.message);
            } finally {
                this.loading = false;
            }
        },

        // ------------------ ABRIR MODAL (CREATE / EDIT) ------------------
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
                    this.formFieldsHtml = res.fields_html || '';

                    // 🔸 Anúncios
                    this.adsFormHtml = res.ads_form_html || '';
                    this.adsGridHtml = res.ads_grid_html || '';
                    this.adsStoreUrl = res.ads_store_url || this.adsStoreUrl;
                    this.currentAdSetId = res.adset_id || id || null;
                    this.adsEnabled = !!res.has_adset;

                    // se estiver editando -> já abre em Anúncios
                    this.activeTab = id ? 'anuncios' : 'conjunto';
                })
                .catch(err => {
                    console.error(err);
                    alert('Erro ao carregar o formulário.');
                    this.closeModal();
                })
                .finally(() => { this.loading = false; });
        },

        async deleteAdFromGrid(adId) {
            alert('Excluir anúncio ainda não foi implementado. ID: ' + adId);
            // Depois implementamos DELETE /anuncios/{id} + refresh do grid.
        },

        async handleFormSubmit() {
            this.loading = true;

            try {
                const formEl = this.$refs.formEl;
                if (!formEl) {
                    throw new Error('Formulário não encontrado.');
                }

                const formData = new FormData(formEl);

                // CSRF
                const token = this.getCsrfToken();
                if (token && !formData.has('_token')) {
                    formData.append('_token', token);
                }

                // PUT/PATCH/DELETE via POST
                if (['PUT', 'PATCH', 'DELETE'].includes(this.formMethod)) {
                    formData.append('_method', this.formMethod);
                }

                const resp = await fetch(this.formAction, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: formData,
                });

                const raw = await resp.text();
                let data;
                try {
                    data = JSON.parse(raw);
                } catch {
                    console.error('Resposta bruta ao salvar conjunto:', raw);
                    throw new Error('Falha ao processar resposta do servidor.');
                }

                if (resp.ok && data?.success) {
                    alert(data.message || 'Salvo com sucesso.');

                    // se for adsets, usamos o closeModal que já força reload
                    if (this.modelName === 'adsets') {
                        this.closeModal(); // closeModal já tem o window.location.reload() pra adsets
                    } else {
                        // outras telas podem só recarregar
                        window.location.reload();
                    }

                    return;
                }

                if (resp.status === 422 && data?.errors) {
                    const flat = Object.values(data.errors).flat().join('\n');
                    throw new Error(flat || (data.message || 'Dados inválidos.'));
                }

                throw new Error(data?.message || 'Erro ao salvar.');
            } catch (e) {
                console.error('Salvar conjunto falhou:', e);
                alert(`Erro ao salvar: ${e.message}`);
            } finally {
                this.loading = false;
            }
        },


        // ------------------ SUBMIT DO FORM DE ANÚNCIO ------------------
        async handleAdFormSubmit() {
            try {
                if (!this.currentAdSetId) {
                    alert('Salve o conjunto antes de criar anúncios.');
                    return;
                }

                const isEditing = !!this.currentAdId;
                const targetUrl = isEditing
                    ? `${this.adsUpdateBaseUrl}/${this.currentAdId}`
                    : this.adsStoreUrl;

                if (!targetUrl) {
                    alert('Endpoint de anúncio não configurado.');
                    return;
                }

                const container = document.getElementById('adsetAdsForm');
                if (!container) {
                    alert('Formulário de anúncio não encontrado.');
                    return;
                }

                const fd = new FormData();

                container.querySelectorAll('input, select, textarea').forEach(el => {
                    if (!el.name) return;

                    if (el.type === 'file') {
                        Array.from(el.files || []).forEach(file => {
                            fd.append(el.name, file);
                        });
                    } else if (el.type === 'checkbox' || el.type === 'radio') {
                        if (el.checked) fd.append(el.name, el.value ?? 'on');
                    } else {
                        fd.append(el.name, el.value ?? '');
                    }
                });

                fd.append('adset_id', this.currentAdSetId);

                const token = this.getCsrfToken();
                if (token) fd.append('_token', token);

                // se for update, manda _method=PUT
                if (isEditing) {
                    fd.append('_method', 'PUT');
                }

                this.loading = true;

                const resp = await fetch(targetUrl, {
                    method: 'POST', // sempre POST, Laravel trata _method
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body: fd,
                });

                const raw = await resp.text();
                let data;
                try { data = JSON.parse(raw); } catch {
                    console.error('Resposta bruta ao salvar anúncio:', raw);
                    throw new Error('Resposta inválida da API ao salvar anúncio.');
                }

                if (resp.ok && data?.success) {
                    alert(data.message || (isEditing ? 'Anúncio atualizado com sucesso.' : 'Anúncio criado com sucesso.'));

                    // atualiza grid se veio HTML
                    if (data.ads_grid_html) {
                        this.adsGridHtml = data.ads_grid_html;
                    }

                    // se era create → limpa campos
                    if (!isEditing) {
                        const c = container;
                        c.querySelectorAll('input[type="text"], input[type="url"], textarea').forEach(el => el.value = '');
                        c.querySelectorAll('input[type="file"]').forEach(el => el.value = '');
                        const statusSelect = c.querySelector('select[name="status"]');
                        if (statusSelect) statusSelect.value = 'PAUSED';
                    }

                    // se era edit → volta pro modo "criar"
                    if (isEditing) {
                        this.currentAdId = null;
                    }

                    return;
                }

                if (resp.status === 422 && data?.errors) {
                    const flat = Object.values(data.errors).flat().join('\n');
                    throw new Error(flat || (data.message || 'Dados inválidos.'));
                }

                throw new Error(data?.message || 'Erro ao salvar anúncio.');
            } catch (e) {
                console.error('Erro ao salvar anúncio:', e);
                alert('Erro ao salvar anúncio: ' + e.message);
            } finally {
                this.loading = false;
            }
        },

        // ------------------ DELETE (GENÉRICO DO GRID) ------------------
        async deleteItem(id) {
            if (!confirm(`Tem certeza que deseja excluir este ${this.modelName}?`)) return;

            try {
                const token = this.getCsrfToken();
                if (!token) throw new Error('Token CSRF não encontrado');

                const resp = await fetch(`${this.deleteBaseUrl}/${id}`, {
                    method: 'POST', // POST + _method=DELETE
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': token,
                    },
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

        async deleteAd(adId) {
            if (!adId) return;

            // 🔒 se já tiver um delete em andamento pra esse anúncio, ignora
            if (this.deletingAdId === adId) {
                console.warn('deleteAd já em andamento para', adId);
                return;
            }
            this.deletingAdId = adId;

            if (!this.adsDeleteBaseUrl) {
                console.error('adsDeleteBaseUrl não definido');
                alert('Endpoint de exclusão de anúncio não configurado.');
                this.deletingAdId = null;
                return;
            }

            // confirma só UMA vez, antes de fazer qualquer request
            if (!confirm('Tem certeza que deseja excluir este anúncio?')) {
                this.deletingAdId = null;
                return;
            }

            try {
                const token = this.getCsrfToken();
                if (!token) throw new Error('Token CSRF não encontrado.');

                const fd = new FormData();
                fd.append('_method', 'DELETE');
                fd.append('_token', token);

                if (this.currentAdSetId) {
                    fd.append('adset_id', this.currentAdSetId);
                }

                this.loading = true;

                const resp = await fetch(`${this.adsDeleteBaseUrl}/${adId}`, {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body: fd,
                });

                const data = await resp.json();
                console.log('Resposta anuncios.destroy:', data);

                if (resp.ok && data.success) {
                    alert(data.message || 'Anúncio excluído com sucesso.');

                    if (data.ads_grid_html) {
                        this.adsGridHtml = data.ads_grid_html;
                    }
                } else {
                    throw new Error(data.message || 'Erro ao excluir anúncio.');
                }
            } catch (e) {
                console.error('Erro ao excluir anúncio:', e);
                alert('Erro ao excluir anúncio: ' + e.message);
            } finally {
                this.loading = false;
                this.deletingAdId = null; // libera o lock pro próximo delete
            }
        },

        // ------------------ FECHAR MODAL ------------------
        closeModal() {
            this.showModal = false;
            this.loading = false;
            this.editingId = null;
            this.modalTitle = '';
            this.formMethod = 'POST';
            this.formAction = '';
            this.formEnctype = 'application/x-www-form-urlencoded';
            this.formFieldsHtml = '';

            // gambiarra para forçar reload na grid de adsets
            if (this.modelName === 'adsets') {
                window.location.reload();
            }
        },

        // ------------------ MÁSCARA TELEFONE ------------------
        onPhoneInput(e) {
            const el = e.target;
            if (el.disabled) return;
            if (!el.classList || !el.classList.contains('phone-mask')) return;

            let v = el.value;
            const hasPlus = v.trim().startsWith('+');
            v = v.replace(/\D+/g, '');
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

        formatPhone(raw) {
            if (!raw) return '';

            const startsPlus = String(raw).trim().startsWith('+');
            let digits = String(raw).replace(/\D+/g, '');

            let hasCountry = false;
            if (startsPlus && digits.startsWith('55')) {
                hasCountry = true;
                digits = digits.slice(2);
            }

            const ddd = digits.slice(0, 2);
            let rest = digits.slice(2);

            if (ddd.length === 0) {
                return hasCountry ? '+55 ' : '';
            }
            if (ddd.length < 2) {
                return (hasCountry ? '+55 ' : '') + `(${ddd}`;
            }

            if (rest.length > 9) rest = rest.slice(0, 9);

            const isNine = rest.length >= 9;
            const left = isNine ? rest.slice(0, 5) : rest.slice(0, 4);
            const right = rest.slice(isNine ? 5 : 4);

            const num = right ? `${left}-${right}` : left;

            return (hasCountry ? '+55 ' : '') + `(${ddd}) ${num}`;
        },
    };
};
