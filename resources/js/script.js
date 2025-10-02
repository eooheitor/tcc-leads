window.gridData = function () {
    return {
        showModal: false,
        loading: false,
        editingId: null,
        modalTitle: "",
        formMethod: "POST",
        imagePreview: null,

        // Injetados pelo Blade
        csrfToken: "",
        storeUrl: "",
        editBaseUrl: "",
        deleteBaseUrl: "",
        modelName: "",

        // Dados do formulário (preenchidos dinamicamente)
        formData: {},

        init() {
            console.log(`Grid iniciado para: ${this.modelName}`);
        },

        getCsrfToken() {
            if (this.csrfToken) return this.csrfToken;

            const metaTag = document.querySelector('meta[name="csrf-token"]');
            if (metaTag) return metaTag.getAttribute("content");

            const hiddenInput = document.querySelector('input[name="_token"]');
            if (hiddenInput) return hiddenInput.value;

            console.error("Token CSRF não encontrado!");
            return null;
        },

        async openModal(id = null) {
            this.showModal = true;
            this.editingId = id;
            this.resetForm();

            if (id) {
                this.modalTitle = `Editar ${this.modelName}`;
                this.formMethod = "PUT";
                await this.loadItemData(id);
            } else {
                this.modalTitle = `Novo ${this.modelName}`;
                this.formMethod = "POST";
            }
        },

        async loadItemData(id) {
            this.loading = true;
            try {
                const response = await fetch(`${this.editBaseUrl}/${id}/edit`, {
                    headers: {
                        "X-Requested-With": "XMLHttpRequest",
                        "Accept": "application/json",
                    }
                });

                const data = await response.json();
                if (!data.success) {
                    throw new Error(data.message || "Erro ao carregar item");
                }

                // Sempre pega o primeiro objeto válido do response (exceto success/message)
                let item = Object.values(data).find(
                    v => v && typeof v === 'object' && !Array.isArray(v)
                );
                if (!item) {
                    throw new Error("Objeto não encontrado.");
                }

                // Preenche todos os campos do form dinamicamente
                this.formData = {};
                // Seleciona o form do modal de forma mais específica
                const modal = document.querySelector('.fixed.inset-0.z-50');
                const form = modal ? modal.querySelector('form') : document.querySelector('form');
                if (form) {
                    setTimeout(() => {
                        Array.from(form.elements).forEach(el => {
                            if (!el.name) return;
                            if (el.type === 'checkbox') {
                                this.formData[el.name] = !!item[el.name];
                                el.checked = !!item[el.name];
                            } else if (el.type === 'file') {
                                // Preview imagem
                                if (item[el.name]) {
                                    this.imagePreview = `/storage/${item[el.name]}`;
                                }
                            } else if (el.tagName === 'SELECT') {
                                this.formData[el.name] = item[el.name] ?? '';
                                el.value = item[el.name] ?? '';
                            } else {
                                this.formData[el.name] = item[el.name] ?? '';
                                el.value = item[el.name] ?? '';
                            }
                        });
                    }, 100); // Pequeno delay para garantir que o form está renderizado
                }
            } catch (error) {
                console.error("Erro ao carregar item:", error);
                alert("Erro ao carregar item: " + error.message);
                this.closeModal();
            } finally {
                this.loading = false;
            }
        },

        async handleFormSubmit() {
            this.loading = true;
            try {
                const token = this.getCsrfToken();
                if (!token) throw new Error("Token CSRF não encontrado.");


                    // Seleciona o form do modal de forma mais específica
                    const modal = document.querySelector('.fixed.inset-0.z-50');
                    const form = modal ? modal.querySelector('form') : document.querySelector('form');
                    const formData = new FormData(form);
                    formData.append('_token', token);
                    if (this.formMethod === "PUT") {
                        formData.append('_method', 'PUT');
                    }

                    // Log para depuração
                    Array.from(form.elements).forEach(el => {
                        if (el.name) console.log('Campo:', el.name, 'Valor:', el.value);
                    });

                const url = this.editingId
                    ? `${this.editBaseUrl}/${this.editingId}`
                    : this.storeUrl;

                const response = await fetch(url, {
                    method: "POST", // Laravel exige POST para PUT/DELETE via formulário
                    headers: { "X-Requested-With": "XMLHttpRequest" },
                    body: formData,
                });

                const data = await response.json();
                if (data.success) {
                    alert(data.message);
                    this.closeModal();
                    window.location.reload();
                } else {
                    throw new Error(data.message || "Erro ao salvar");
                }
            } catch (error) {
                console.error("Erro ao salvar:", error);
                alert("Erro ao salvar: " + error.message);
            } finally {
                this.loading = false;
            }
        },

        async deleteItem(id) {
            if (!confirm(`Tem certeza que deseja excluir este ${this.modelName}?`)) {
                return;
            }

            try {
                const token = this.getCsrfToken();
                if (!token) throw new Error("Token CSRF não encontrado");

                const response = await fetch(`${this.deleteBaseUrl}/${id}`, {
                    method: "DELETE",
                    headers: {
                        "X-Requested-With": "XMLHttpRequest",
                        "X-CSRF-TOKEN": token,
                        "Content-Type": "application/json",
                    },
                });

                const data = await response.json();
                if (data.success) {
                    alert(data.message);
                    window.location.reload();
                } else {
                    throw new Error(data.message || "Erro ao excluir");
                }
            } catch (error) {
                console.error("Erro ao excluir:", error);
                alert("Erro ao excluir: " + error.message);
            }
        },

        handleImageChange(event) {
            const file = event.target.files[0];
            if (file) {
                this.imagePreview = URL.createObjectURL(file);
            } else {
                this.imagePreview = null;
            }
        },

        resetForm() {
            // Limpa todos os campos do form dinamicamente
            this.formData = {};
            this.imagePreview = null;
            const form = document.querySelector('form');
            if (form) {
                Array.from(form.elements).forEach(el => {
                    if (!el.name) return;
                    if (el.type === 'checkbox') {
                        el.checked = false;
                    } else if (el.type === 'file') {
                        el.value = '';
                    } else {
                        el.value = '';
                    }
                });
            }
        },

        closeModal() {
            this.showModal = false;
            this.loading = false;
            this.editingId = null;
            this.modalTitle = "";
            this.formMethod = "POST";
            this.resetForm();
        },
    };
};
