

import Alpine from 'alpinejs';

window.Alpine = Alpine;

  Alpine.data('checkoutShipping', (endpoint, csrfToken, subtotal, initialCourier, initialPayment, initialProvince, initialCity, initialPostalCode, komerceEnabled, destinationEndpoint, initialDestinationId, initialDistrict, initialSubdistrict, initialVoucherCode, initialPaymentDestination) => ({
    voucherCode: initialVoucherCode || '',
    voucherError: '',
    province: initialProvince || '',
    city: initialCity || '',
    district: initialDistrict || '',
    subdistrict: initialSubdistrict || '',
    postalCode: initialPostalCode || '',
    destinationId: initialDestinationId || '',
    searchQuery: initialSubdistrict || '',
    destinations: [],
    searching: false,
    searchError: '',
    searchRequestId: 0,
    selectedCourier: initialCourier || '',
    payment: initialPayment || '',
    paymentDestination: initialPaymentDestination || '',
    options: [],
    weightGrams: 0,
    usesAssumedWeight: false,
    loading: false,
    error: '',
    requestId: 0,

    init() {
        if (this.province && this.city && this.postalCode && (!komerceEnabled || this.destinationId)) {
            this.refresh();
        }
    },

    invalidateDestination() {
        this.requestId++;
        this.destinationId = '';
        this.options = [];
        this.selectedCourier = '';
        this.loading = false;
    },

    async searchDestinations() {
        const currentSearchId = ++this.searchRequestId;
        this.invalidateDestination();
        this.destinations = [];
        this.searchError = '';

        if (this.searchQuery.trim().length < 3) {
            return;
        }

        this.searching = true;
        try {
            const response = await fetch(`${destinationEndpoint}?search=${encodeURIComponent(this.searchQuery.trim())}`, {
                headers: { 'Accept': 'application/json' },
            });
            if (!response.ok) {
                throw new Error('Pencarian wilayah gagal. Coba lagi.');
            }
            const result = await response.json();
            if (currentSearchId === this.searchRequestId) {
                this.destinations = result.destinations;
                if (this.destinations.length === 0) {
                    this.searchError = 'Wilayah tidak ditemukan. Coba nama kelurahan atau kode pos lain.';
                }
            }
        } catch (error) {
            if (currentSearchId === this.searchRequestId) {
                this.searchError = error.message;
            }
        } finally {
            if (currentSearchId === this.searchRequestId) {
                this.searching = false;
            }
        }
    },

    chooseDestination(destination) {
        this.searchRequestId++;
        this.destinationId = String(destination.id);
        this.searchQuery = destination.label;
        this.province = destination.province_name;
        this.city = destination.city_name;
        this.district = destination.district_name;
        this.subdistrict = destination.subdistrict_name;
        this.postalCode = destination.zip_code;
        this.destinations = [];
        this.refresh();
    },

    get selectedOption() {
        return this.options.find((option) => option.courier === this.selectedCourier);
    },

    get canSubmit() {
        return Boolean(this.selectedOption) && !this.loading && Boolean(this.payment) && (this.payment !== 'transfer' || Boolean(this.paymentDestination)) && !(this.selectedCourier === 'gosend' && this.payment === 'cod');
    },

    get estimatedTotal() {
        return this.selectedOption?.payable_total ?? subtotal + (this.selectedOption?.cost || 0);
    },

    formatRupiah(amount) {
        return `Rp${new Intl.NumberFormat('id-ID').format(amount)}`;
    },

    applyVoucher() {
        this.voucherCode = this.voucherCode.trim().toUpperCase();

        if (!this.province.trim() || !this.city.trim() || !/^\d{5}$/.test(this.postalCode.trim()) || (komerceEnabled && !this.destinationId)) {
            this.voucherError = 'Lengkapi alamat pengiriman terlebih dahulu untuk memeriksa kode voucher.';
            return;
        }

        this.refresh();
    },

    async refresh() {
        const previousCourier = this.selectedCourier;
        const currentRequestId = ++this.requestId;
        this.options = [];
        this.selectedCourier = '';
        this.error = '';

        if (!this.province.trim() || !this.city.trim() || !/^\d{5}$/.test(this.postalCode.trim()) || (komerceEnabled && !this.destinationId)) {
            return;
        }

        this.loading = true;

        try {
            const response = await fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({
                    shipping_province: this.province,
                    shipping_city: this.city,
                    shipping_postal_code: this.postalCode,
                    shipping_destination_id: this.destinationId,
                    voucher_code: this.voucherCode.trim(),
                }),
            });

            if (!response.ok) {
                throw new Error('Estimasi ongkir belum dapat dihitung. Periksa alamat dan coba lagi.');
            }

            const estimate = await response.json();
            if (currentRequestId !== this.requestId) {
                return;
            }
            this.options = Object.values(estimate.options);
            this.weightGrams = estimate.weight_grams;
            this.usesAssumedWeight = estimate.uses_assumed_weight;
            this.voucherError = estimate.voucher_error || '';
            this.selectedCourier = this.options.some((option) => option.courier === previousCourier)
                ? previousCourier
                : (this.options[0]?.courier || '');
            if (this.selectedCourier === 'gosend' && this.payment === 'cod') {
                this.payment = 'transfer';
            }
        } catch (error) {
            if (currentRequestId === this.requestId) {
                this.error = error.message;
            }
        } finally {
            if (currentRequestId === this.requestId) {
                this.loading = false;
            }
        }
    },
}));

Alpine.data('paymentCopy', () => ({
    async copy(event) {
        const button = event.currentTarget;
        const value = button.dataset.copyValue;

        try {
            if (navigator.clipboard && window.isSecureContext) {
                await navigator.clipboard.writeText(value);
            } else {
                const input = document.createElement('textarea');
                input.value = value;
                input.style.position = 'fixed';
                input.style.opacity = '0';
                document.body.appendChild(input);
                input.select();
                const copied = document.execCommand('copy');
                document.body.removeChild(input);
                if (!copied) {
                    throw new Error('Copy failed');
                }
            }

            const originalText = button.dataset.originalText || button.textContent.trim();
            button.dataset.originalText = originalText;
            button.textContent = 'Tersalin';
            window.setTimeout(() => {
                button.textContent = originalText;
            }, 1600);
        } catch {
            button.textContent = 'Gagal menyalin';
            window.setTimeout(() => {
                button.textContent = button.dataset.originalText || 'Salin';
            }, 1600);
        }
    },
}));

Alpine.start();
