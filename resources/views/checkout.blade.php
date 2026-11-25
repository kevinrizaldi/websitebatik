<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Selesaikan Pembayaran - Hamzah Style</title>
    <meta name="description" content="Selesaikan pembayaran pesanan busana batik autentik ramah lingkungan di Hamzah Style dengan metode pembayaran aman dan terenkripsi.">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400;1,600&display=swap" rel="stylesheet">

    <!-- Styles & Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Leaflet CSS & JS for OpenStreetMap Pin Point -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <!-- Midtrans Snap (popup bawaan Midtrans) -->
    <script src="{{ config('midtrans.snap_js_url') }}" data-client-key="{{ config('midtrans.client_key') }}"></script>

    <style>
        [x-cloak] { display: none !important; }
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #FAF7F2;
            color: #26211D;
        }
        .font-serif-title {
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>
<body class="antialiased bg-[#FAF7F2] text-[#26211D] min-h-screen selection:bg-[#B58742] selection:text-white"
      x-init="fetchRajaOngkirCosts()"
      x-data="{
          mobileMenuOpen: false,
          userMenu: false,
          searchModal: false,
          editAddressModal: false,
          modalView: 'list', // 'list' or 'form'
          formMode: 'add',   // 'add' or 'edit'
          editingAddressId: null,
          modalSaving: false,
          isSearchingLocation: false,
          locationAutoFound: false,
          autoFoundLabel: '',
          searchDebounceTimer: null,
          regionPickerOpen: false,
          activeRegionTab: 'provinsi',
          searchRegion: '',
          toastMessage: '',
          isSubmitting: false,

          showToast(msg) {
              this.toastMessage = msg;
              setTimeout(() => { this.toastMessage = ''; }, 3500);
          },

@php
    $defaultAlamat = (isset($alamats) && $alamats->isNotEmpty()) 
        ? ($alamats->where('is_utama', true)->first() ?: $alamats->first())
        : null;
@endphp
          // Customer & Shipping Address State
          addressData: {
              id: {{ $defaultAlamat ? $defaultAlamat->id : 'null' }},
              recipient: '{{ $defaultAlamat ? addslashes($defaultAlamat->penerima) : (Auth::check() ? addslashes(Auth::user()->name) : '') }}',
              label: '{{ $defaultAlamat ? addslashes($defaultAlamat->label_alamat) : 'Rumah' }}',
              phone: '{{ $defaultAlamat ? addslashes($defaultAlamat->no_telepon) : (Auth::check() && Auth::user()->phone ? addslashes(Auth::user()->phone) : '') }}',
              address: '{{ $defaultAlamat ? addslashes($defaultAlamat->alamat_lengkap) : '' }}',
              city: '{{ $defaultAlamat ? addslashes($defaultAlamat->kota ?? '') : '' }}',
              cityId: '{{ $defaultAlamat ? ($defaultAlamat->city_id ?? '') : '' }}',
              district: '{{ $defaultAlamat ? addslashes($defaultAlamat->kecamatan ?? $defaultAlamat->district ?? '') : '' }}',
              districtId: '{{ $defaultAlamat ? ($defaultAlamat->district_id ?? '') : '' }}',
              province: '{{ $defaultAlamat ? addslashes($defaultAlamat->provinsi ?? '') : '' }}',
              provinceId: '{{ $defaultAlamat ? ($defaultAlamat->province_id ?? '') : '' }}',
              postalCode: '{{ $defaultAlamat ? addslashes($defaultAlamat->kode_pos ?? '') : '' }}',
              latitude: {{ $defaultAlamat && $defaultAlamat->latitude !== null ? (float)$defaultAlamat->latitude : 'null' }},
              longitude: {{ $defaultAlamat && $defaultAlamat->longitude !== null ? (float)$defaultAlamat->longitude : 'null' }},
              isUtama: {{ $defaultAlamat && $defaultAlamat->is_utama ? 'true' : 'false' }},
              hasAddress: {{ $defaultAlamat ? 'true' : 'false' }}
          },

          selectedAddressId: {{ $defaultAlamat ? $defaultAlamat->id : 'null' }},

          get fullAddress() {
              if (!this.addressData.address) return '';
              let parts = [this.addressData.address];
              const dist = (this.addressData.district || '').trim();
              const city = (this.addressData.city || '').trim();
              const prov = (this.addressData.province || '').trim();
              const post = (this.addressData.postalCode || '').trim();
              
              const lowerAddr = this.addressData.address.toLowerCase();
              if (dist && !lowerAddr.includes(dist.toLowerCase())) parts.push('Kec. ' + dist);
              if (city && !lowerAddr.includes(city.toLowerCase())) parts.push(city);
              if (prov && !lowerAddr.includes(prov.toLowerCase())) parts.push(prov);
              if (post && !lowerAddr.includes(post.toLowerCase())) parts.push(post);
              return parts.join(', ');
          },

          // Alamat tersimpan customer untuk dipilih
          savedAddresses: [
              @if(isset($alamats) && $alamats->isNotEmpty())
                  @foreach($alamats as $alm)
                  {
                      id: {{ $alm->id }},
                      label: '{{ addslashes($alm->label_alamat) }}',
                      penerima: '{{ addslashes($alm->penerima) }}',
                      phone: '{{ addslashes($alm->no_telepon) }}',
                      address: '{{ addslashes($alm->alamat_lengkap) }}',
                      city: '{{ addslashes($alm->kota ?? '') }}',
                      cityId: '{{ $alm->city_id ?? '' }}',
                      district: '{{ addslashes($alm->kecamatan ?? $alm->district ?? '') }}',
                      districtId: '{{ $alm->district_id ?? '' }}',
                      province: '{{ addslashes($alm->provinsi ?? '') }}',
                      provinceId: '{{ $alm->province_id ?? '' }}',
                      postalCode: '{{ addslashes($alm->kode_pos ?? '') }}',
                      latitude: {{ $alm->latitude !== null ? (float)$alm->latitude : 'null' }},
                      longitude: {{ $alm->longitude !== null ? (float)$alm->longitude : 'null' }},
                      isUtama: {{ $alm->is_utama ? 'true' : 'false' }}
                  }@if(!$loop->last),@endif
                  @endforeach
              @endif
          ],

          selectAddress(id) {
              const picked = this.savedAddresses.find(a => a.id === Number(id));
              if (!picked) return;
              this.selectedAddressId = picked.id;
              this.addressData.id = picked.id;
              this.addressData.recipient = picked.penerima;
              this.addressData.label = picked.label || 'Rumah';
              this.addressData.phone = picked.phone;
              this.addressData.address = picked.address;
              this.addressData.city = picked.city;
              this.addressData.cityId = picked.cityId || '';
              this.addressData.district = picked.district || '';
              this.addressData.districtId = picked.districtId || '';
              this.addressData.province = picked.province || '';
              this.addressData.provinceId = picked.provinceId || '';
              this.addressData.postalCode = picked.postalCode || '';
              this.addressData.latitude = picked.latitude || null;
              this.addressData.longitude = picked.longitude || null;
              this.addressData.isUtama = picked.isUtama;
              this.addressData.hasAddress = true;
              this.fetchRajaOngkirCosts();
          },

          tempAddress: {
              label: '',
              recipient: '',
              phone: '',
              city: '',
              cityId: '',
              district: '',
              districtId: '',
              province: '',
              provinceId: '',
              postalCode: '',
              address: '',
              latitude: null,
              longitude: null,
              isUtama: false
          },

          checkoutMap: null,
          checkoutMarker: null,
          geoLocating: false,
          resolvingAddress: false,
          pinAddressHint: '',

          init() {
              this.$watch('editAddressModal', val => {
                  if (val && this.modalView === 'form') {
                      setTimeout(() => this.initOrRefreshCheckoutMap(), 250);
                  }
              });
              this.$watch('modalView', val => {
                  if (val === 'form') {
                      setTimeout(() => this.initOrRefreshCheckoutMap(), 250);
                  }
              });
          },

          initOrRefreshCheckoutMap() {
              const el = document.getElementById('checkout-leaflet-map-picker');
              if (!el || typeof L === 'undefined') return;

              const hasCoords = this.tempAddress.latitude !== null && this.tempAddress.longitude !== null && !isNaN(this.tempAddress.latitude);
              const defaultLat = hasCoords ? parseFloat(this.tempAddress.latitude) : -7.5666;
              const defaultLng = hasCoords ? parseFloat(this.tempAddress.longitude) : 110.8290;

              if (!this.checkoutMap) {
                  this.checkoutMap = L.map(el, {
                      center: [defaultLat, defaultLng],
                      zoom: hasCoords ? 16 : 13,
                      scrollWheelZoom: false
                  });

                  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                      attribution: '&copy; OpenStreetMap contributors',
                      maxZoom: 19
                  }).addTo(this.checkoutMap);

                  this.checkoutMarker = L.marker([defaultLat, defaultLng], {
                      draggable: true
                  }).addTo(this.checkoutMap);

                  if (!hasCoords) {
                      this.checkoutMarker.setOpacity(0.4);
                  }

                  this.checkoutMarker.on('dragend', (e) => {
                      const pos = e.target.getLatLng();
                      this.setCheckoutCoordinates(pos.lat, pos.lng);
                  });

                  this.checkoutMap.on('click', (e) => {
                      this.setCheckoutCoordinates(e.latlng.lat, e.latlng.lng);
                      this.checkoutMarker.setLatLng(e.latlng);
                      this.checkoutMarker.setOpacity(1);
                  });
              } else {
                  this.checkoutMap.invalidateSize();
                  this.checkoutMap.setView([defaultLat, defaultLng], hasCoords ? 16 : 13);
                  this.checkoutMarker.setLatLng([defaultLat, defaultLng]);
                  this.checkoutMarker.setOpacity(hasCoords ? 1 : 0.4);
              }
          },

          async setCheckoutCoordinates(lat, lng) {
              this.tempAddress.latitude = Number(lat.toFixed(7));
              this.tempAddress.longitude = Number(lng.toFixed(7));
              if (this.checkoutMarker) {
                  this.checkoutMarker.setOpacity(1);
              }
              this.resolvingAddress = true;
              try {
                  const res = await fetch(`{{ route('alamat.reverse-geocode') }}?lat=${lat}&lng=${lng}`);
                  const data = await res.json();
                  if (data && data.success && data.display_name) {
                      this.pinAddressHint = data.display_name;
                      if (!this.tempAddress.postalCode && data.postcode) {
                          this.tempAddress.postalCode = data.postcode;
                      }
                  }
              } catch (e) {
                  // Non-blocking
              } finally {
                  this.resolvingAddress = false;
              }
          },

          getCheckoutCurrentLocation() {
              if (!navigator.geolocation) {
                  alert('Browser Anda tidak mendukung deteksi lokasi otomatis.');
                  return;
              }
              this.geoLocating = true;

              const onSuccess = (pos) => {
                  this.geoLocating = false;
                  const lat = pos.coords.latitude;
                  const lng = pos.coords.longitude;
                  this.setCheckoutCoordinates(lat, lng);
                  if (this.checkoutMap) {
                      this.checkoutMap.setView([lat, lng], 17);
                      if (this.checkoutMarker) {
                          this.checkoutMarker.setLatLng([lat, lng]);
                          this.checkoutMarker.setOpacity(1);
                      }
                      setTimeout(() => {
                          if (this.checkoutMap) this.checkoutMap.invalidateSize();
                      }, 100);
                  }
              };

              const onFinalError = (err) => {
                  this.geoLocating = false;
                  let msg = 'Tidak dapat mendeteksi lokasi otomatis.';
                  if (err && err.code === 1) {
                      msg = 'Izin akses lokasi ditolak oleh browser.\n\nCara mengizinkan:\n1. Klik ikon pengaturan / gembok di samping link localhost:8000.\n2. Ubah izin Location menjadi Allow (Izinkan).\n3. Muat ulang halaman.';
                  } else if (err && err.code === 2) {
                      msg = 'Layanan lokasi tidak tersedia di perangkat Anda (fitur Location Windows tidak aktif atau PC tidak memiliki modul WiFi/GPS).\n\nTips: Anda tetap bisa menentukan titik dengan langsung mengklik atau menggeser pin merah pada peta.';
                  } else if (err && err.code === 3) {
                      msg = 'Waktu pencarian lokasi habis.\n\nTips: Anda dapat langsung mengklik atau menggeser pin merah pada peta di lokasi rumah Anda.';
                  } else {
                      msg = 'Gagal mendeteksi lokasi otomatis. Anda dapat langsung mengklik atau menggeser pin pada peta.';
                  }
                  this.showToast(msg);
              };

              navigator.geolocation.getCurrentPosition(
                  onSuccess,
                  (err) => {
                      if (err && (err.code === 2 || err.code === 3)) {
                          navigator.geolocation.getCurrentPosition(
                              onSuccess,
                              onFinalError,
                              { enableHighAccuracy: false, timeout: 8000, maximumAge: 60000 }
                          );
                      } else {
                          onFinalError(err);
                      }
                  },
                  { enableHighAccuracy: true, timeout: 6000, maximumAge: 0 }
              );
          },

          clearCheckoutPin() {
              this.tempAddress.latitude = null;
              this.tempAddress.longitude = null;
              this.pinAddressHint = '';
              if (this.checkoutMarker) {
                  this.checkoutMarker.setOpacity(0.4);
              }
          },

          provincesList: [],
          citiesList: [],
          districtsList: [],
          postalCodesList: [],
          loadingProvinces: false,
          loadingCities: false,
          loadingDistricts: false,
          loadingPostalCodes: false,

          async loadProvincesForCheckout() {
              if (this.provincesList.length > 0) return;
              this.loadingProvinces = true;
              try {
                  const res = await fetch('{{ route('rajaongkir.provinces') }}');
                  const data = await res.json();
                  if (data.success && Array.isArray(data.provinces)) {
                      this.provincesList = data.provinces;
                  }
              } catch (e) {
                  console.error('Failed loading provinces', e);
              } finally {
                  this.loadingProvinces = false;
              }
          },

          async loadCitiesForCheckout(provinceId) {
              if (!provinceId) {
                  this.citiesList = [];
                  return;
              }
              this.loadingCities = true;
              try {
                  const res = await fetch('{{ route('rajaongkir.cities') }}?province_id=' + provinceId);
                  const data = await res.json();
                  if (data.success && Array.isArray(data.cities)) {
                      this.citiesList = data.cities;
                  }
              } catch (e) {
                  console.error('Failed loading cities', e);
              } finally {
                  this.loadingCities = false;
              }
          },

          async onCheckoutProvinceChange() {
              const selected = this.provincesList.find(p => String(p.province_id) === String(this.tempAddress.provinceId));
              this.tempAddress.province = selected ? selected.province : '';
              this.tempAddress.cityId = '';
              this.tempAddress.city = '';
              await this.loadCitiesForCheckout(this.tempAddress.provinceId);
          },

          get checkoutRegionDisplay() {
              const parts = [];
              if (this.tempAddress.province) parts.push(this.tempAddress.province);
              if (this.tempAddress.city) parts.push(this.tempAddress.city);
              if (this.tempAddress.district) parts.push(this.tempAddress.district);
              if (this.tempAddress.postalCode) parts.push(this.tempAddress.postalCode);
              return parts.join(', ');
          },

          get filteredCheckoutProvinces() {
              if (!this.searchRegion) return this.provincesList;
              const s = this.searchRegion.toLowerCase();
              return this.provincesList.filter(p => (p.province || '').toLowerCase().includes(s));
          },

          get filteredCheckoutCities() {
              if (!this.searchRegion) return this.citiesList;
              const s = this.searchRegion.toLowerCase();
              return this.citiesList.filter(c => ((c.type || '') + ' ' + (c.city_name || '')).toLowerCase().includes(s));
          },

          get filteredCheckoutDistricts() {
              if (!this.searchRegion) return this.districtsList;
              const s = this.searchRegion.toLowerCase();
              return this.districtsList.filter(d => (d.district_name || '').toLowerCase().includes(s));
          },

          get filteredCheckoutPostalCodes() {
              if (!this.searchRegion) return this.postalCodesList;
              const s = this.searchRegion.toLowerCase();
              return this.postalCodesList.filter(item => 
                  (item.postal_code || '').toLowerCase().includes(s) || 
                  (item.subdistrict || '').toLowerCase().includes(s)
              );
          },

          async loadDistrictsForCheckout(cityId) {
              if (!cityId) {
                  this.districtsList = [];
                  return;
              }
              this.loadingDistricts = true;
              try {
                  const res = await fetch('{{ route('rajaongkir.districts') }}?city_id=' + cityId);
                  const data = await res.json();
                  if (data.success && Array.isArray(data.districts)) {
                      this.districtsList = data.districts;
                  }
              } catch (e) {
                  console.error('Failed loading districts for checkout', e);
              } finally {
                  this.loadingDistricts = false;
              }
          },

          async selectCheckoutProvince(p) {
              this.tempAddress.provinceId = String(p.province_id);
              this.tempAddress.province = p.province;
              this.tempAddress.cityId = '';
              this.tempAddress.city = '';
              this.tempAddress.districtId = '';
              this.tempAddress.district = '';
              this.tempAddress.postalCode = '';
              this.postalCodesList = [];
              this.searchRegion = '';
              this.activeRegionTab = 'kota';
              await this.loadCitiesForCheckout(p.province_id);
          },

          selectCheckoutCity(c) {
              this.tempAddress.cityId = String(c.city_id);
              const prefix = c.type ? c.type + ' ' : '';
              this.tempAddress.city = prefix + c.city_name;
              this.tempAddress.districtId = '';
              this.tempAddress.district = '';
              this.postalCodesList = [];
              if (c.postal_code) {
                  this.tempAddress.postalCode = c.postal_code;
              }
              this.searchRegion = '';
              this.activeRegionTab = 'kecamatan';
              this.loadDistrictsForCheckout(c.city_id);
          },

          selectCheckoutDistrict(d) {
              this.tempAddress.districtId = String(d.district_id);
              this.tempAddress.district = d.district_name;
              this.searchRegion = '';
              this.activeRegionTab = 'kodepos';
              this.loadCheckoutPostalCodes(d.district_name, this.tempAddress.city, this.tempAddress.cityId);
              this.searchCheckoutAddressLocation(false);
          },

          async loadCheckoutPostalCodes(districtName, cityName, cityId) {
              this.loadingPostalCodes = true;
              try {
                  const params = new URLSearchParams();
                  if (districtName) params.append('district_name', districtName);
                  if (cityName) params.append('city_name', cityName);
                  if (cityId) params.append('city_id', cityId);

                  const res = await fetch('{{ route('rajaongkir.postal-codes') }}?' + params.toString());
                  const data = await res.json();
                  if (data.success && Array.isArray(data.items)) {
                      this.postalCodesList = data.items;
                      if (data.default_postal_code) {
                          this.tempAddress.postalCode = data.default_postal_code;
                      }
                  }
              } catch (e) {
                  console.error('Failed loading postal codes for checkout', e);
              } finally {
                  this.loadingPostalCodes = false;
              }
          },

          selectCheckoutPostalCode(code) {
              this.tempAddress.postalCode = code;
              this.regionPickerOpen = false;
          },

          onCheckoutCityChange() {
              const selected = this.citiesList.find(c => String(c.city_id) === String(this.tempAddress.cityId));
              if (selected) {
                  const prefix = selected.type ? selected.type + ' ' : '';
                  this.tempAddress.city = prefix + selected.city_name;
                  if (selected.postal_code && !this.tempAddress.postalCode) {
                      this.tempAddress.postalCode = selected.postal_code;
                  }
                  this.searchCheckoutAddressLocation(false);
              }
          },

          onCheckoutAddressInput() {
              clearTimeout(this.searchDebounceTimer);
              this.locationAutoFound = false;
              const text = (this.tempAddress.address || '').trim();
              if (text.length < 3) return;

              this.searchDebounceTimer = setTimeout(() => {
                  this.searchCheckoutAddressLocation(false);
              }, 700);
          },

          async searchCheckoutAddressLocation(isManual = false) {
              const jalan = (this.tempAddress.address || '').trim();
              const kec = (this.tempAddress.district || '').trim();
              const kota = (this.tempAddress.city || '').trim();
              const prov = (this.tempAddress.province || '').trim();

              if (!jalan && !kec && !kota) return;

              this.isSearchingLocation = true;
              this.locationAutoFound = false;

              try {
                  const params = new URLSearchParams();
                  if (jalan) params.append('jalan', jalan);
                  if (kec) params.append('kecamatan', kec);
                  if (kota) params.append('kota', kota);
                  if (prov) params.append('provinsi', prov);

                  const res = await fetch('{{ route('alamat.geocode') }}?' + params.toString());
                  const data = await res.json();

                  if (data && data.success && data.found) {
                      const lat = parseFloat(data.latitude);
                      const lon = parseFloat(data.longitude);
                      this.tempAddress.latitude = Number(lat.toFixed(7));
                      this.tempAddress.longitude = Number(lon.toFixed(7));
                      this.pinAddressHint = data.display_name;
                      this.autoFoundLabel = data.display_name;
                      this.locationAutoFound = true;

                      if (this.checkoutMap) {
                          const targetZoom = jalan ? 16 : 13;
                          this.checkoutMap.setView([lat, lon], targetZoom);
                          if (this.checkoutMarker) {
                              this.checkoutMarker.setLatLng([lat, lon]);
                              this.checkoutMarker.setOpacity(1);
                          }
                          setTimeout(() => {
                              if (this.checkoutMap) this.checkoutMap.invalidateSize();
                          }, 150);
                      }
                  } else if (isManual) {
                      this.showToast('Lokasi jalan belum ditemukan otomatis di peta. Anda dapat langsung mengklik titik lokasi pada peta.');
                  }
              } catch (e) {
                  console.error('Checkout geocoding failed', e);
              } finally {
                  this.isSearchingLocation = false;
              }
          },

          openEditAddress() {
              if (this.savedAddresses.length === 0) {
                  this.openAddAddress();
              } else {
                  this.modalView = 'list';
                  this.editAddressModal = true;
              }
          },

          async openAddAddress() {
              this.modalView = 'form';
              this.formMode = 'add';
              this.editingAddressId = null;
              this.pinAddressHint = '';
              this.isSearchingLocation = false;
              this.locationAutoFound = false;
              this.autoFoundLabel = '';
              this.regionPickerOpen = false;
              this.activeRegionTab = 'provinsi';
              this.searchRegion = '';
              this.postalCodesList = [];
              clearTimeout(this.searchDebounceTimer);
              this.tempAddress = {
                  label: 'Rumah',
                  recipient: '',
                  phone: '',
                  city: '',
                  cityId: '',
                  district: '',
                  districtId: '',
                  province: '',
                  provinceId: '',
                  postalCode: '',
                  address: '',
                  latitude: null,
                  longitude: null,
                  isUtama: false
              };
              this.editAddressModal = true;
              await this.loadProvincesForCheckout();
              setTimeout(() => this.initOrRefreshCheckoutMap(), 250);
          },

          async openEditSingleAddress(addr) {
              this.modalView = 'form';
              this.formMode = 'edit';
              this.editingAddressId = addr.id;
              this.pinAddressHint = '';
              this.isSearchingLocation = false;
              this.locationAutoFound = false;
              this.autoFoundLabel = '';
              this.regionPickerOpen = false;
              this.activeRegionTab = 'provinsi';
              this.searchRegion = '';
              this.postalCodesList = [];
              clearTimeout(this.searchDebounceTimer);
              this.tempAddress = {
                  label: addr.label || 'Rumah',
                  recipient: addr.penerima,
                  phone: addr.phone,
                  city: addr.city,
                  cityId: addr.cityId || '',
                  district: addr.district || '',
                  districtId: addr.districtId || '',
                  province: addr.province || '',
                  provinceId: addr.provinceId || '',
                  postalCode: addr.postalCode || '',
                  address: addr.address,
                  latitude: addr.latitude || null,
                  longitude: addr.longitude || null,
                  isUtama: addr.isUtama
              };
              this.editAddressModal = true;
              await this.loadProvincesForCheckout();
              if (this.tempAddress.provinceId) {
                  await this.loadCitiesForCheckout(this.tempAddress.provinceId);
                  if (this.tempAddress.cityId) {
                      await this.loadDistrictsForCheckout(this.tempAddress.cityId);
                  }
              }
              setTimeout(() => this.initOrRefreshCheckoutMap(), 250);
          },

          saveAddress() {
              const recipient = (this.tempAddress.recipient || '').trim();
              const phone = (this.tempAddress.phone || '').trim();
              const address = (this.tempAddress.address || '').trim();
              const city = (this.tempAddress.city || '').trim();
              const label = (this.tempAddress.label || '').trim() || 'Rumah';
              const province = (this.tempAddress.province || '').trim();
              const postalCode = (this.tempAddress.postalCode || '').trim();
              const isUtama = !!this.tempAddress.isUtama;

              if (!recipient || !phone || !address || !city) {
                  this.showToast('Mohon lengkapi Nama Penerima, No HP, Kota, dan Alamat Lengkap');
                  return;
              }

              if (!/^[0-9]{10,15}$/.test(phone)) {
                  this.showToast('Nomor WhatsApp / HP hanya boleh berisi angka (10 - 15 digit)');
                  return;
              }

              if (postalCode && postalCode.length !== 5) {
                  this.showToast('Kode pos harus berupa 5 digit angka');
                  return;
              }

              this.modalSaving = true;
              const isEdit = this.formMode === 'edit' && this.editingAddressId;
              const url = isEdit ? '{{ url('/alamat') }}/' + this.editingAddressId : '{{ route('alamat.store') }}';
              const method = isEdit ? 'PUT' : 'POST';

              const payload = {
                  label_alamat: label,
                  penerima: recipient,
                  no_telepon: phone,
                  kota: city,
                  district: this.tempAddress.district || null,
                  kecamatan: this.tempAddress.district || null,
                  district_id: this.tempAddress.districtId ? parseInt(this.tempAddress.districtId) : null,
                  provinsi: province || null,
                  province_id: this.tempAddress.provinceId ? parseInt(this.tempAddress.provinceId) : null,
                  city_id: this.tempAddress.cityId ? parseInt(this.tempAddress.cityId) : null,
                  kode_pos: postalCode || null,
                  alamat_lengkap: address,
                  latitude: this.tempAddress.latitude ? parseFloat(this.tempAddress.latitude) : null,
                  longitude: this.tempAddress.longitude ? parseFloat(this.tempAddress.longitude) : null,
                  is_utama: isUtama
              };

              fetch(url, {
                  method: method,
                  headers: {
                      'Content-Type': 'application/json',
                      'X-CSRF-TOKEN': '{{ csrf_token() }}',
                      'Accept': 'application/json'
                  },
                  body: JSON.stringify(payload)
              })
              .then(async res => {
                  this.modalSaving = false;
                  const data = await res.json().catch(() => ({}));
                  if (!res.ok) {
                      const msg = (data.errors ? Object.values(data.errors).flat()[0] : null) || data.message || 'Gagal menyimpan alamat.';
                      this.showToast(msg);
                      return;
                  }

                  if (data.success && data.alamat) {
                      const savedObj = {
                          id: data.alamat.id,
                          label: data.alamat.label_alamat,
                          penerima: data.alamat.penerima,
                          phone: data.alamat.no_telepon,
                          address: data.alamat.alamat_lengkap,
                          city: data.alamat.kota,
                          cityId: data.alamat.city_id || '',
                          district: data.alamat.kecamatan || data.alamat.district || '',
                          districtId: data.alamat.district_id || '',
                          province: data.alamat.provinsi || '',
                          provinceId: data.alamat.province_id || '',
                          postalCode: data.alamat.kode_pos || '',
                          latitude: data.alamat.latitude ? parseFloat(data.alamat.latitude) : null,
                          longitude: data.alamat.longitude ? parseFloat(data.alamat.longitude) : null,
                          isUtama: !!data.alamat.is_utama
                      };

                      if (isEdit) {
                          const idx = this.savedAddresses.findIndex(a => a.id === savedObj.id);
                          if (idx !== -1) {
                              this.savedAddresses[idx] = savedObj;
                          }

                          if (savedObj.isUtama) {
                              this.savedAddresses.forEach(a => {
                                  if (a.id !== savedObj.id) a.isUtama = false;
                              });
                          }

                          if (this.selectedAddressId === savedObj.id) {
                              this.selectAddress(savedObj.id);
                          }
                          this.editAddressModal = false;
                          this.modalView = 'list';
                          this.showToast('Alamat pengiriman berhasil diperbarui!');
                      } else {
                          this.savedAddresses.unshift(savedObj);

                          if (savedObj.isUtama) {
                              this.savedAddresses.forEach(a => {
                                  if (a.id !== savedObj.id) a.isUtama = false;
                              });
                          }

                          // Alamat baru tidak langsung dipake; kembali ke daftar alamat
                          this.modalView = 'list';
                          this.showToast('Alamat baru berhasil ditambahkan.');
                      }
                  }
              })
              .catch(() => {
                  this.modalSaving = false;
                  this.showToast('Terjadi kendala jaringan saat menyimpan alamat.');
              });
          },

          setPrimaryAddress(id) {
              fetch('{{ url('/alamat') }}/' + id + '/default', {
                  method: 'PATCH',
                  headers: {
                      'Content-Type': 'application/json',
                      'X-CSRF-TOKEN': '{{ csrf_token() }}',
                      'Accept': 'application/json'
                  }
              })
              .then(res => res.json())
              .then(data => {
                  if (data.success) {
                      this.savedAddresses.forEach(a => {
                          a.isUtama = (a.id === id);
                      });
                      if (this.selectedAddressId === id) {
                          this.addressData.isUtama = true;
                      }
                      this.showToast('Alamat utama berhasil diubah.');
                  }
              })
              .catch(() => {
                  this.showToast('Gagal mengubah alamat utama.');
              });
          },

          deleteAddress(id) {
              if (!confirm('Hapus alamat ini dari daftar tersimpan?')) return;
              fetch('{{ url('/alamat') }}/' + id, {
                  method: 'DELETE',
                  headers: {
                      'Content-Type': 'application/json',
                      'X-CSRF-TOKEN': '{{ csrf_token() }}',
                      'Accept': 'application/json'
                  }
              })
              .then(res => res.json())
              .then(data => {
                  if (data.success) {
                      this.savedAddresses = this.savedAddresses.filter(a => a.id !== id);
                      if (this.selectedAddressId === id) {
                          if (this.savedAddresses.length > 0) {
                              this.selectAddress(this.savedAddresses[0].id);
                          } else {
                              this.addressData.id = null;
                              this.addressData.recipient = '{{ Auth::check() ? addslashes(Auth::user()->name) : '' }}';
                              this.addressData.label = '';
                              this.addressData.phone = '{{ Auth::check() && Auth::user()->phone ? addslashes(Auth::user()->phone) : '' }}';
                              this.addressData.address = '';
                              this.addressData.city = '';
                              this.addressData.province = '';
                              this.addressData.postalCode = '';
                              this.addressData.isUtama = false;
                              this.addressData.hasAddress = false;
                              this.selectedAddressId = null;
                          }
                      }
                      this.showToast('Alamat berhasil dihapus.');
                  }
              })
              .catch(() => {
                  this.showToast('Gagal menghapus alamat.');
              });
          },

          // Selected Items in Checkout
          items: [
              @if(isset($cartItems) && $cartItems->isNotEmpty())
                  @foreach($cartItems as $item)
                  @php
                      $prod = $item->produk;
                      $imgUrl = $prod ? $prod->gambar_url : asset('images/beranda/folded-shirts.jpg');
                      $kategori = $prod ? $prod->kategori : 'Koleksi Batik';
                      $nama = $prod ? $prod->nama : 'Busana Batik Nusantara';
                      $harga = $prod ? (float)$prod->harga : 0;
                  @endphp
                  {
                      id: {{ $item->id }},
                      produk_id: {{ $prod ? $prod->id : 'null' }},
                      nama: '{{ addslashes($nama) }}',
                      kategori: '{{ addslashes($kategori) }}',
                      ukuran: '{{ $item->ukuran ?? 'All Size' }}',
                      varian: '{{ addslashes($item->ukuran ? ('Ukuran: ' . $item->ukuran) : ($item->varian ? trim(str_replace('• Lengan Panjang', '', $item->varian)) : 'Ukuran: All Size')) }}',
                      qty: {{ $item->qty }},
                      harga: {{ $harga }},
                      gambar: '{{ $imgUrl }}'
                  },
                  @endforeach
              @endif
          ],

          // RajaOngkir Shipping State & Selection
          shippingServices: [
              { code: 'JNE', name: 'Jalur Nugraha Ekakurir (JNE)', service: 'REG', description: 'Layanan Reguler', cost: 20000, etd: '1-2 hari' },
              { code: 'JNE', name: 'Jalur Nugraha Ekakurir (JNE)', service: 'YES', description: 'Yakin Esok Sampai', cost: 32000, etd: '1 hari' },
              { code: 'POS', name: 'POS Indonesia (POS)', service: 'Pos Reguler', description: 'Pos Reguler Domestik', cost: 18000, etd: '2-3 hari' },
              { code: 'TIKI', name: 'Citra Van Titipan Kilat (TIKI)', service: 'REG', description: 'Regular Service', cost: 19000, etd: '2 hari' }
          ],
          selectedShippingKey: 'JNE-REG',
          loadingShipping: false,
          courierFilter: 'all',
          isRajaOngkirLive: false,

          get filteredShippingServices() {
              if (this.courierFilter === 'all') {
                  return this.shippingServices;
              }
              return this.shippingServices.filter(s => s.code.toUpperCase() === this.courierFilter.toUpperCase());
          },

          get selectedShippingService() {
              const found = this.shippingServices.find(s => (s.code + '-' + s.service) === this.selectedShippingKey);
              return found || this.shippingServices[0] || null;
          },

          // Pembayaran: seluruh transaksi via Midtrans (metode dipilih di popup Snap).
          paymentMethodName: 'Midtrans Gateway',

          // Notes for seller
          sellerNotes: '',

          // Calculations
          get totalItemQty() {
              return this.items.reduce((sum, item) => sum + item.qty, 0);
          },

          get rawSubtotal() {
              return this.items.reduce((sum, item) => sum + (item.harga * item.qty), 0);
          },

          get totalWeightGrams() {
              return Math.max(1000, this.items.reduce((sum, item) => sum + (item.qty * 300), 0));
          },

          get totalWeightKg() {
              return (this.totalWeightGrams / 1000).toFixed(1);
          },

          get shippingCost() {
              return this.selectedShippingService ? this.selectedShippingService.cost : 20000;
          },

          get grandTotal() {
              return Math.max(0, this.rawSubtotal + this.shippingCost);
          },

          async fetchRajaOngkirCosts() {
              let dest = this.addressData.cityId;
              if (!dest && this.selectedAddressId) {
                  const found = this.savedAddresses.find(a => a.id === Number(this.selectedAddressId));
                  if (found && found.cityId) dest = found.cityId;
              }
              this.loadingShipping = true;
              try {
                  const res = await fetch('{{ route('rajaongkir.cost') }}', {
                      method: 'POST',
                      headers: {
                          'Content-Type': 'application/json',
                          'X-CSRF-TOKEN': '{{ csrf_token() }}',
                          'Accept': 'application/json'
                      },
                      body: JSON.stringify({
                          destination: dest || 444,
                          weight: this.totalWeightGrams,
                          courier: ['jne', 'pos', 'tiki'],
                          city_name: this.addressData.city || '',
                          province_name: this.addressData.province || '',
                          province_id: this.addressData.provinceId || null
                      })
                  });
                  const data = await res.json();
                  if (data.success && Array.isArray(data.services) && data.services.length > 0) {
                      this.shippingServices = data.services;
                      this.isRajaOngkirLive = !!data.is_live;
                      if (!this.shippingServices.some(s => (s.code + '-' + s.service) === this.selectedShippingKey)) {
                          this.selectedShippingKey = this.shippingServices[0].code + '-' + this.shippingServices[0].service;
                      }
                  }
              } catch (e) {
                  console.error('Failed fetching RajaOngkir cost', e);
              } finally {
                  this.loadingShipping = false;
              }
          },

          formatRupiah(amount) {
              return 'Rp ' + Number(amount).toLocaleString('id-ID');
          },

          // Submit Order to Database
          submitOrder() {
              if (!this.addressData.recipient || !this.addressData.phone || !this.addressData.address) {
                  this.showToast('Mohon lengkapi alamat pengiriman terlebih dahulu.');
                  this.openEditAddress();
                  return;
              }

              // Metode Midtrans (VA, QRIS, Kartu Kredit) butuh user login
              const isMidtransMethod = this.selectedPayment !== 'manual_bca';
              const isLoggedIn = {{ Auth::check() ? 'true' : 'false' }};

              if (isMidtransMethod && !isLoggedIn) {
                  this.showToast('Silakan login terlebih dahulu untuk menggunakan metode pembayaran ini.');
                  setTimeout(() => { window.location.href = '{{ route('login') }}'; }, 1800);
                  return;
              }

              this.isSubmitting = true;
              const selectedSvc = this.selectedShippingService;
              const shippingLabel = selectedSvc ? (selectedSvc.code + ' - ' + selectedSvc.service + ' (' + selectedSvc.description + ')') : 'JNE Reguler';
              const shippingPrice = selectedSvc ? selectedSvc.cost : 20000;

              fetch('{{ route('checkout.store') }}', {
                  method: 'POST',
                  headers: {
                      'Content-Type': 'application/json',
                      'X-CSRF-TOKEN': '{{ csrf_token() }}',
                      'Accept': 'application/json'
                  },
                  body: JSON.stringify({
                      customer_name: this.addressData.recipient,
                      phone: this.addressData.phone,
                      address: this.fullAddress,
                      city: this.addressData.city,
                      district: this.addressData.district || null,
                      kecamatan: this.addressData.district || null,
                      province: this.addressData.province,
                      city_id: this.addressData.cityId || null,
                      district_id: this.addressData.districtId || null,
                      province_id: this.addressData.provinceId || null,
                      postal_code: this.addressData.postalCode,
                      alamat_id: this.selectedAddressId,
                      shipping_option: shippingLabel,
                      shipping_cost: shippingPrice,
                      payment_method: this.paymentMethodName,
                      notes: this.sellerNotes
                  })
              })
              .then(async res => {
                  const data = await res.json().catch(() => ({}));
                  if (!res.ok || !data.success) {
                      this.isSubmitting = false;
                      const errorMsg = (data.errors ? Object.values(data.errors).flat()[0] : null) || data.message || 'Gagal memproses transaksi.';
                      this.showToast(errorMsg);
                      return;
                  }

                  // Langsung buka popup bawaan Midtrans bila token tersedia.
                  if (data.snap_token && typeof window.snap !== 'undefined' && !String(data.snap_token).startsWith('MOCK_SNAP_')) {
                      window.snap.pay(data.snap_token, {
                          onSuccess: () => {
                              window.location.href = '{{ route('midtrans.finish') }}?order_id=' + encodeURIComponent(data.order_code) + '&status=success';
                          },
                          onPending: () => {
                              this.showToast('Menunggu pembayaran Anda diselesaikan.');
                              setTimeout(() => {
                                  window.location.href = '{{ route('pesanan.index') }}';
                              }, 1500);
                          },
                          onError: () => {
                              this.isSubmitting = false;
                              this.showToast('Pembayaran gagal. Lanjutkan dari halaman pembayaran.');
                              setTimeout(() => {
                                  window.location.href = data.redirect_url || '{{ route('pesanan.index') }}';
                              }, 1500);
                          },
                          onClose: () => {
                              this.isSubmitting = false;
                              this.showToast('Popup Midtrans ditutup. Lanjutkan kapan saja dari halaman pembayaran.');
                              setTimeout(() => {
                                  window.location.href = data.redirect_url || '{{ route('pesanan.index') }}';
                              }, 1500);
                          }
                      });
                      return;
                  }
                  window.location.href = data.redirect_url || '{{ route('pesanan.index') }}';
              })
              .catch(() => {
                  this.isSubmitting = false;
                  this.showToast('Terjadi kendala koneksi saat menghubungi server.');
              });
          }
      }">

    <!-- Toast Notification -->
    <div x-cloak x-show="toastMessage" 
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="opacity-0 translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200 transform"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-4"
         class="fixed bottom-6 right-6 z-50 bg-[#1F1916] text-white px-5 py-3.5 rounded-2xl shadow-2xl flex items-center gap-3 border border-stone-700">
        <svg class="w-5 h-5 text-amber-400 shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
        <span x-text="toastMessage" class="text-sm font-medium"></span>
    </div>

    <!-- ================= MODAL UBAH / TAMBAH ALAMAT ================= -->
    <div x-cloak x-show="editAddressModal" 
         class="fixed inset-0 z-50 overflow-y-auto"
         aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="editAddressModal" 
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="editAddressModal = false"
                 class="fixed inset-0 bg-stone-900/60 backdrop-blur-xs transition-opacity"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="editAddressModal" 
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl sm:w-full border border-stone-200">
                
                <!-- Modal Header -->
                <div class="px-6 py-5 bg-[#FAF7F2] border-b border-[#ECE4D8] flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <button x-show="modalView === 'form' && savedAddresses.length > 0" 
                                @click="modalView = 'list'" 
                                type="button" 
                                class="p-1.5 -ml-1 text-stone-500 hover:text-stone-900 rounded-lg hover:bg-stone-200/50 transition flex items-center gap-1 text-xs font-semibold mr-1"
                                title="Kembali ke daftar alamat">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                            <span class="hidden sm:inline">Daftar</span>
                        </button>
                        <div>
                            <h3 class="text-base font-bold text-stone-900 flex items-center gap-2">
                                <span x-show="modalView === 'list'">Pilih Alamat Pengiriman</span>
                                <span x-show="modalView === 'form'" x-text="formMode === 'edit' ? 'Ubah Alamat Pengiriman' : 'Tambah Alamat Baru'"></span>
                            </h3>
                            <p class="text-xs text-stone-500 mt-0.5">
                                <span x-show="modalView === 'list'">Pilih alamat tersimpan atau tambahkan alamat baru</span>
                                <span x-show="modalView === 'form'" x-text="formMode === 'edit' ? 'Perbarui informasi alamat pengiriman Anda' : 'Lengkapi data alamat untuk tujuan pesanan Anda'"></span>
                            </p>
                        </div>
                    </div>
                    <button @click="editAddressModal = false" class="p-1.5 text-stone-400 hover:text-stone-700 rounded-lg hover:bg-stone-200/50 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <!-- ================= VIEW 1: DAFTAR ALAMAT TERSIMPAN ================= -->
                <div x-show="modalView === 'list'" class="p-6 space-y-4">
                    <div class="flex items-center justify-between">
                        <h4 class="text-xs font-bold text-stone-800 uppercase tracking-wider">
                            Pilih Alamat Tersimpan
                        </h4>
                        <span class="text-[11px] font-semibold text-stone-500" x-text="savedAddresses.length + ' alamat tersedia'"></span>
                    </div>

                    <div x-show="savedAddresses.length > 0" class="space-y-3 max-h-80 overflow-y-auto pr-1">
                        <template x-for="addr in savedAddresses" :key="addr.id">
                            <div :class="selectedAddressId === addr.id ? 'border-[#B58742] bg-[#FAF7F2] ring-2 ring-[#B58742]/20 shadow-xs' : 'border-stone-200 hover:border-stone-300 bg-white'"
                                 class="p-4 rounded-2xl border transition-all flex flex-col gap-2.5">
                                <div class="flex items-start justify-between gap-3">
                                    <button @click="selectAddress(addr.id); editAddressModal = false; showToast('Alamat pengiriman berhasil dipilih!')" 
                                            type="button" 
                                            class="flex-1 text-left flex items-start gap-3">
                                        <!-- Radio Selector Circle -->
                                        <div class="mt-0.5 w-4 h-4 rounded-full border-2 flex items-center justify-center shrink-0 transition"
                                             :class="selectedAddressId === addr.id ? 'border-[#B58742] bg-[#B58742]' : 'border-stone-300'">
                                            <div x-show="selectedAddressId === addr.id" class="w-1.5 h-1.5 rounded-full bg-white"></div>
                                        </div>

                                        <div class="flex-1 min-w-0">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <span class="font-bold text-sm text-stone-900" x-text="addr.penerima"></span>
                                                <span class="px-2 py-0.5 rounded-md bg-[#201A17] text-[#E5C38E] text-[10px] font-bold uppercase tracking-wider" x-text="addr.label || 'Rumah'"></span>
                                                <span x-show="addr.isUtama" class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-bold uppercase">Utama</span>
                                            </div>
                                            <p class="text-xs text-stone-500 font-medium mt-0.5" x-text="addr.phone"></p>
                                            <p class="text-xs text-stone-700 leading-relaxed mt-1" x-text="addr.address + (addr.district ? ', Kec. ' + addr.district : '') + (addr.city ? ', ' + addr.city : '') + (addr.province ? ', ' + addr.province : '') + (addr.postalCode ? ' ' + addr.postalCode : '')"></p>
                                            <template x-if="addr.latitude && addr.longitude">
                                                <div class="mt-1 flex items-center gap-1 text-[11px] text-[#8C6226]">
                                                    <svg class="h-3.5 w-3.5 text-[#B58742]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
                                                    <span>Titik Maps Terpasang</span>
                                                </div>
                                            </template>
                                        </div>
                                    </button>

                                    <!-- Action Buttons -->
                                    <div class="flex items-center gap-1.5 shrink-0 pt-0.5">
                                        <button @click.stop="openEditSingleAddress(addr)" 
                                                type="button" 
                                                title="Ubah data alamat ini"
                                                class="px-2.5 py-1 text-xs font-semibold text-stone-700 hover:text-stone-950 bg-white hover:bg-stone-100 rounded-lg transition border border-stone-200">
                                            Ubah
                                        </button>
                                        <button @click.stop="deleteAddress(addr.id)" 
                                                type="button" 
                                                title="Hapus alamat ini" 
                                                class="p-1.5 text-stone-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </div>
                                </div>

                                <!-- Card Footer: Set Primary / Select -->
                                <div class="flex items-center justify-between pt-2 border-t border-stone-100 text-xs">
                                    <div>
                                        <button x-show="!addr.isUtama" 
                                                @click.stop="setPrimaryAddress(addr.id)" 
                                                type="button" 
                                                class="text-[11px] font-semibold text-stone-500 hover:text-[#B58742] transition underline">
                                            Jadikan Alamat Utama
                                        </button>
                                        <span x-show="addr.isUtama" class="text-[11px] font-semibold text-emerald-700 flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                            Alamat Utama Anda
                                        </span>
                                    </div>

                                    <button @click="selectAddress(addr.id); editAddressModal = false; showToast('Alamat pengiriman berhasil dipilih!')"
                                            type="button"
                                            :class="selectedAddressId === addr.id ? 'bg-[#201A17] text-white' : 'bg-white hover:bg-stone-100 text-stone-800 border border-stone-200'"
                                            class="px-3.5 py-1 rounded-xl text-xs font-bold transition">
                                        <span x-text="selectedAddressId === addr.id ? '✓ Dipilih' : 'Gunakan Alamat'"></span>
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Button Tambah Alamat Baru -->
                    <button @click="openAddAddress()" type="button"
                            class="w-full py-3.5 rounded-2xl border-2 border-dashed border-stone-300 hover:border-[#B58742] hover:bg-[#FAF7F2] text-stone-700 hover:text-stone-900 text-xs font-bold transition flex items-center justify-center gap-2">
                        <span class="w-5 h-5 rounded-full bg-amber-100 text-[#B58742] flex items-center justify-center font-bold text-sm leading-none">+</span>
                        Tambah Alamat Baru
                    </button>
                </div>

                <!-- ================= VIEW 2: FORM TAMBAH / UBAH ALAMAT ================= -->
                <div x-show="modalView === 'form'" class="p-6 space-y-4">
                    <!-- Baris 1: Nama Penerima & Nomor Telepon -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        <div>
                            <label class="block text-xs font-bold text-stone-700 uppercase tracking-wider mb-1.5">
                                Nama Lengkap <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" 
                                   x-model="tempAddress.recipient"
                                   @input="tempAddress.recipient = tempAddress.recipient.replace(/[^a-zA-Z\s\'.()\-]/g, '')"
                                   placeholder="Nama Lengkap"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 text-sm focus:outline-none focus:ring-2 focus:ring-[#B58742] bg-[#FAF7F2]/40">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-stone-700 uppercase tracking-wider mb-1.5">
                                Nomor Telepon <span class="text-rose-500">*</span>
                            </label>
                            <input type="tel" 
                                   inputmode="numeric"
                                   maxlength="15"
                                   x-model="tempAddress.phone"
                                   @input="tempAddress.phone = tempAddress.phone.replace(/\D/g, '')"
                                   placeholder="Nomor Telepon (08xxxxxxxxxx)"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 text-sm focus:outline-none focus:ring-2 focus:ring-[#B58742] bg-[#FAF7F2]/40">
                        </div>
                    </div>

                    <!-- Baris 2: Pemilih Wilayah Terpadu ala Shopee (Provinsi, Kota, Kecamatan, Kode Pos) -->
                    <div class="space-y-1.5" @click.outside="if (regionPickerOpen && tempAddress.city) regionPickerOpen = false">
                        <div class="flex items-center justify-between mb-0.5">
                            <label class="block text-xs font-bold uppercase tracking-wider text-stone-700">
                                Provinsi, Kota, Kabupaten, Kecamatan, Kode Pos <span class="text-rose-500">*</span>
                            </label>
                            <button type="button"
                                    x-show="tempAddress.province"
                                    @click="tempAddress.provinceId = ''; tempAddress.province = ''; tempAddress.cityId = ''; tempAddress.city = ''; tempAddress.districtId = ''; tempAddress.district = ''; tempAddress.postalCode = ''; regionPickerOpen = true; activeRegionTab = 'provinsi';"
                                    class="text-[11px] font-medium text-stone-400 hover:text-stone-700">
                                Reset
                            </button>
                        </div>
                        
                        <div @click="regionPickerOpen = !regionPickerOpen; if (regionPickerOpen) { loadProvincesForCheckout(); if (tempAddress.cityId && !tempAddress.district) { activeRegionTab = 'kecamatan'; loadDistrictsForCheckout(tempAddress.cityId); } else if (tempAddress.provinceId && !tempAddress.city) { activeRegionTab = 'kota'; } else if (!tempAddress.provinceId) { activeRegionTab = 'provinsi'; } searchRegion = ''; }"
                             class="flex items-center justify-between w-full rounded-xl border border-stone-300 bg-[#FAF7F2]/40 px-3.5 py-2.5 text-sm cursor-pointer hover:border-[#B58742] transition select-none">
                            <span :class="checkoutRegionDisplay ? 'text-stone-900 font-medium' : 'text-stone-400'"
                                  x-text="checkoutRegionDisplay || 'Pilih Provinsi, Kota, Kabupaten, Kecamatan, Kode Pos'"></span>
                            <div class="flex items-center gap-1.5 text-stone-400">
                                <svg class="h-4 w-4 transition-transform duration-200"
                                     :class="regionPickerOpen ? 'rotate-180 text-stone-700' : ''"
                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
                                </svg>
                            </div>
                        </div>

                        <!-- Panel Tab Selector Terbuka secara IN-FLOW / Inline -->
                        <div x-show="regionPickerOpen"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 -translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             class="w-full rounded-2xl border border-stone-200 bg-[#FAF7F2]/70 p-3 shadow-xs">
                            
                            <!-- Input Pencarian di dalam Dropdown -->
                            <div class="relative mb-2">
                                <input type="text"
                                       x-model="searchRegion"
                                       :placeholder="'Cari ' + (activeRegionTab === 'provinsi' ? 'provinsi' : (activeRegionTab === 'kota' ? 'kota atau kabupaten' : (activeRegionTab === 'kecamatan' ? 'kecamatan' : 'kode pos'))) + '...'"
                                       class="w-full rounded-xl border border-stone-200 bg-white px-3 py-1.5 text-xs text-stone-800 placeholder-stone-400 focus:border-[#201A17] focus:bg-white focus:outline-none">
                                <svg class="absolute right-2.5 top-2.5 h-3.5 w-3.5 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </div>

                            <!-- Header Tab: Provinsi | Kota/Kabupaten | Kecamatan | Kode Pos -->
                            <div class="grid grid-cols-4 border-b border-stone-200/80 text-xs font-semibold">
                                <button type="button"
                                        @click="activeRegionTab = 'provinsi'; searchRegion = '';"
                                        :class="activeRegionTab === 'provinsi' ? 'border-b-2 border-[#201A17] text-[#201A17] font-bold' : 'text-stone-400 hover:text-stone-600'"
                                        class="pb-2 px-1 text-center transition">
                                    Provinsi
                                </button>
                                <button type="button"
                                        @click="if (tempAddress.provinceId) { activeRegionTab = 'kota'; searchRegion = ''; }"
                                        :disabled="!tempAddress.provinceId"
                                        :class="activeRegionTab === 'kota' ? 'border-b-2 border-[#201A17] text-[#201A17] font-bold' : (tempAddress.provinceId ? 'text-stone-400 hover:text-stone-600' : 'text-stone-300 cursor-not-allowed')"
                                        class="pb-2 px-1 text-center transition">
                                    <span class="hidden sm:inline">Kota/Kabupaten</span>
                                    <span class="sm:hidden">Kota/Kab</span>
                                </button>
                                <button type="button"
                                        @click="if (tempAddress.cityId) { activeRegionTab = 'kecamatan'; searchRegion = ''; }"
                                        :disabled="!tempAddress.cityId"
                                        :class="activeRegionTab === 'kecamatan' ? 'border-b-2 border-[#201A17] text-[#201A17] font-bold' : (tempAddress.cityId ? 'text-stone-400 hover:text-stone-600' : 'text-stone-300 cursor-not-allowed')"
                                        class="pb-2 px-1 text-center transition">
                                    Kecamatan
                                </button>
                                <button type="button"
                                        @click="if (tempAddress.cityId) { activeRegionTab = 'kodepos'; }"
                                        :disabled="!tempAddress.cityId"
                                        :class="activeRegionTab === 'kodepos' ? 'border-b-2 border-[#201A17] text-[#201A17] font-bold' : (tempAddress.cityId ? 'text-stone-400 hover:text-stone-600' : 'text-stone-300 cursor-not-allowed')"
                                        class="pb-2 px-1 text-center transition">
                                    Kode Pos
                                </button>
                            </div>

                            <!-- Tab Provinsi -->
                            <div x-show="activeRegionTab === 'provinsi'" class="mt-2 max-h-48 overflow-y-auto overscroll-contain space-y-0.5 pr-1">
                                <div x-show="loadingProvinces" class="py-6 text-center text-xs text-stone-400">
                                    Memuat daftar provinsi...
                                </div>
                                <template x-for="p in filteredCheckoutProvinces" :key="p.province_id">
                                    <button type="button"
                                            @click="selectCheckoutProvince(p)"
                                            :class="String(tempAddress.provinceId) === String(p.province_id) ? 'bg-[#FAF3EA] text-[#8C6226] font-bold' : 'text-stone-700 hover:bg-stone-100/70 bg-white'"
                                            class="w-full text-left px-3 py-2 text-xs rounded-lg transition flex items-center justify-between border border-transparent hover:border-stone-200">
                                        <span x-text="p.province"></span>
                                        <span x-show="String(tempAddress.provinceId) === String(p.province_id)" class="text-[#8C6226]">✓</span>
                                    </button>
                                </template>
                                <div x-show="!loadingProvinces && filteredCheckoutProvinces.length === 0" class="py-4 text-center text-xs text-stone-400">
                                    Tidak ada provinsi yang cocok.
                                </div>
                            </div>

                            <!-- Tab Kota/Kabupaten -->
                            <div x-show="activeRegionTab === 'kota'" class="mt-2 max-h-48 overflow-y-auto overscroll-contain space-y-0.5 pr-1">
                                <div x-show="loadingCities" class="py-6 text-center text-xs text-stone-400">
                                    Memuat daftar kota & kabupaten...
                                </div>
                                <template x-for="c in filteredCheckoutCities" :key="c.city_id + '-' + c.type + '-' + c.city_name">
                                    <button type="button"
                                            @click="selectCheckoutCity(c)"
                                            :class="String(tempAddress.cityId) === String(c.city_id) && tempAddress.city === ((c.type ? c.type + ' ' : '') + c.city_name) ? 'bg-[#FAF3EA] text-[#8C6226] font-bold' : 'text-stone-700 hover:bg-stone-100/70 bg-white'"
                                            class="w-full text-left px-3 py-2 text-xs rounded-lg transition flex items-center justify-between border border-transparent hover:border-stone-200">
                                        <span x-text="(c.type ? c.type + ' ' : '') + c.city_name"></span>
                                        <span class="text-[10px] text-stone-400 font-mono" x-show="c.postal_code" x-text="c.postal_code"></span>
                                    </button>
                                </template>
                                <div x-show="!loadingCities && filteredCheckoutCities.length === 0" class="py-4 text-center text-xs text-stone-400">
                                    Tidak ada kota atau kabupaten yang cocok.
                                </div>
                            </div>

                            <!-- Tab Kecamatan -->
                            <div x-show="activeRegionTab === 'kecamatan'" class="mt-2 max-h-48 overflow-y-auto overscroll-contain space-y-0.5 pr-1">
                                <div x-show="loadingDistricts" class="py-6 text-center text-xs text-stone-400">
                                    Memuat daftar kecamatan...
                                </div>
                                <template x-for="d in filteredCheckoutDistricts" :key="d.district_id">
                                    <button type="button"
                                            @click="selectCheckoutDistrict(d)"
                                            :class="String(tempAddress.districtId) === String(d.district_id) ? 'bg-[#FAF3EA] text-[#8C6226] font-bold' : 'text-stone-700 hover:bg-stone-100/70 bg-white'"
                                            class="w-full text-left px-3 py-2 text-xs rounded-lg transition flex items-center justify-between border border-transparent hover:border-stone-200">
                                        <span x-text="d.district_name"></span>
                                        <span x-show="String(tempAddress.districtId) === String(d.district_id)" class="text-[#8C6226]">✓</span>
                                    </button>
                                </template>
                                <div x-show="!loadingDistricts && filteredCheckoutDistricts.length === 0" class="py-3 text-center space-y-2">
                                    <p class="text-xs text-stone-500">Kecamatan tidak ditemukan dalam daftar otomatis.</p>
                                    <div class="flex items-center gap-2 px-2">
                                        <input type="text"
                                               x-model="tempAddress.district"
                                               placeholder="Ketik nama kecamatan..."
                                               class="w-full rounded-xl border border-stone-200 bg-white px-3 py-1.5 text-xs focus:border-stone-400 focus:outline-none">
                                        <button type="button"
                                                @click="activeRegionTab = 'kodepos'; searchCheckoutAddressLocation(false);"
                                                class="rounded-xl bg-[#201A17] text-white px-3 py-1.5 text-xs font-semibold hover:bg-stone-800 transition whitespace-nowrap">
                                            Pilih
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Tab Kode Pos Otomatis ala Shopee -->
                            <div x-show="activeRegionTab === 'kodepos'" class="mt-2 space-y-2">
                                <div x-show="loadingPostalCodes" class="py-6 text-center text-xs text-stone-400">
                                    <svg class="w-4 h-4 animate-spin inline mr-1.5 text-[#B58742]" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                    Memuat daftar kode pos...
                                </div>

                                <div x-show="!loadingPostalCodes && postalCodesList.length > 0" class="max-h-48 overflow-y-auto overscroll-contain space-y-0.5 pr-1">
                                    <template x-for="item in filteredCheckoutPostalCodes" :key="item.postal_code + '-' + (item.subdistrict || '')">
                                        <button type="button"
                                                @click="selectCheckoutPostalCode(item.postal_code)"
                                                :class="tempAddress.postalCode === item.postal_code ? 'bg-[#FAF3EA] text-[#8C6226] font-bold' : 'text-stone-700 hover:bg-stone-100/70 bg-white'"
                                                class="w-full text-left px-3 py-2 text-xs rounded-lg transition flex items-center justify-between border border-transparent hover:border-stone-200">
                                            <div class="flex items-center gap-2">
                                                <span class="font-mono font-bold" x-text="item.postal_code"></span>
                                                <span class="text-[11px] font-normal" :class="tempAddress.postalCode === item.postal_code ? 'text-[#8C6226]' : 'text-stone-500'" x-show="item.subdistrict" x-text="'(' + item.subdistrict + ')'"></span>
                                            </div>
                                            <span x-show="tempAddress.postalCode === item.postal_code" class="text-[#8C6226]">✓</span>
                                        </button>
                                    </template>
                                </div>

                                <div x-show="!loadingPostalCodes && filteredCheckoutPostalCodes.length === 0 && postalCodesList.length > 0" class="py-4 text-center text-xs text-stone-400">
                                    Tidak ada kode pos yang cocok dengan pencarian.
                                </div>

                                <div x-show="!loadingPostalCodes && postalCodesList.length === 0" class="py-3 text-center space-y-2">
                                    <p class="text-xs text-stone-500">Kode pos tidak ditemukan dalam daftar otomatis.</p>
                                    <div class="flex items-center gap-2 px-2">
                                        <input type="text"
                                               x-model="tempAddress.postalCode"
                                               maxlength="5"
                                               placeholder="Ketik 5 digit kode pos..."
                                               class="w-full rounded-xl border border-stone-200 bg-white px-3 py-1.5 text-xs focus:border-stone-400 focus:outline-none">
                                        <button type="button"
                                                @click="regionPickerOpen = false;"
                                                class="rounded-xl bg-[#201A17] text-white px-3 py-1.5 text-xs font-semibold hover:bg-stone-800 transition whitespace-nowrap">
                                            Pilih
                                        </button>
                                    </div>
                                </div>

                                <div x-show="!loadingPostalCodes && postalCodesList.length > 0" class="pt-2 border-t border-stone-200/60 flex items-center justify-between text-xs">
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-stone-500 text-[11px]">Kode Pos:</span>
                                        <span class="font-mono font-bold text-stone-900 bg-stone-100 px-2 py-0.5 rounded" x-text="tempAddress.postalCode || '-'"></span>
                                    </div>
                                    <button type="button"
                                            @click="regionPickerOpen = false;"
                                            class="rounded-xl bg-[#201A17] text-white px-4 py-1.5 text-xs font-semibold hover:bg-stone-800 transition shadow-xs">
                                        OK / Selesai
                                    </button>
                                </div>
                            </div>

                            <!-- Footer Panel: Tombol Tutup -->
                            <div class="mt-2 pt-2 border-t border-stone-200/80 flex items-center justify-between text-[11px]">
                                <span class="text-stone-400">Pilih wilayah dari tab di atas</span>
                                <button type="button"
                                        @click="regionPickerOpen = false"
                                        class="font-semibold text-stone-600 hover:text-stone-900 transition">
                                    Tutup Pilihan ✕
                                </button>
                            </div>
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-xs font-bold text-stone-800 uppercase tracking-wider">
                                Alamat Lengkap <span class="text-rose-500">*</span>
                            </label>
                            <button type="button" 
                                    @click="searchCheckoutAddressLocation(true)" 
                                    :disabled="isSearchingLocation || !tempAddress.address" 
                                    class="inline-flex items-center gap-1 text-[11px] font-semibold text-[#8C6226] hover:text-[#B58742] disabled:opacity-40 transition">
                                <svg class="w-3 h-3 animate-spin" x-show="isSearchingLocation" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                <svg class="w-3 h-3" x-show="!isSearchingLocation" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                                <span x-text="isSearchingLocation ? 'Mencari...' : 'Cari di Peta'"></span>
                            </button>
                        </div>
                        <textarea x-model="tempAddress.address"
                                  @input="onCheckoutAddressInput()"
                                  rows="3"
                                  placeholder="Nama Jalan, Nomor Gedung/Rumah, RT/RW, Kelurahan, Kecamatan, Patokan"
                                  class="w-full px-3.5 py-2.5 rounded-xl border border-stone-300 text-sm focus:outline-none focus:ring-2 focus:ring-[#B58742] bg-[#FAF7F2]/40"></textarea>
                        
                        <!-- Notifikasi Otomatis ala Shopee -->
                        <div x-show="isSearchingLocation" class="mt-1 flex items-center gap-1.5 text-[11px] text-[#8C6226]">
                            <svg class="h-3 w-3 animate-spin text-[#B58742]" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                            <span>Menyesuaikan lokasi peta dengan alamat...</span>
                        </div>
                        <div x-show="locationAutoFound && !isSearchingLocation" class="mt-1 flex items-center gap-1.5 text-[11px] text-emerald-700">
                            <svg class="h-3.5 w-3.5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                            <span>Titik peta disesuaikan otomatis dengan alamat. Geser penanda jika posisi kurang pas.</span>
                        </div>
                    </div>

                    <!-- Titik Pin Peta (OpenStreetMap via Leaflet) -->
                    <div class="space-y-2 pt-2 border-t border-stone-200/80">
                        <div class="flex items-center justify-between">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-stone-700">
                                    Titik Pin Lokasi (Peta)
                                </label>
                                <p class="text-[11px] text-stone-500">
                                    Klik peta atau geser pin merah ke lokasi tujuan pengiriman.
                                </p>
                            </div>
                            <button type="button" 
                                    @click="getCheckoutCurrentLocation()" 
                                    :disabled="geoLocating" 
                                    class="inline-flex items-center gap-1.5 rounded-lg bg-[#FAF3EA] border border-[#DECDB8] px-2.5 py-1 text-[11px] font-semibold text-[#8C6226] hover:bg-[#F2E5D3] transition">
                                <svg class="w-3.5 h-3.5 animate-spin" x-show="geoLocating" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                <svg class="w-3.5 h-3.5 text-[#B58742]" x-show="!geoLocating" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
                                <span x-text="geoLocating ? 'Mencari...' : 'Gunakan Lokasi Saya'"></span>
                            </button>
                        </div>

                        <!-- Leaflet Map Container -->
                        <div class="relative z-0 isolate overflow-hidden rounded-2xl border border-stone-200 bg-stone-100" style="isolation: isolate;">
                            <div id="checkout-leaflet-map-picker" style="height: 220px; width: 100%; min-height: 200px;" class="w-full z-0"></div>
                            <div x-show="resolvingAddress" class="absolute top-2 right-2 bg-white/90 backdrop-blur-xs px-2 py-1 rounded text-[10px] font-semibold text-stone-600 shadow-xs border border-stone-200 z-10 flex items-center gap-1">
                                <span class="animate-pulse">●</span> Mencari nama jalan...
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center justify-between gap-1 text-[11px]">
                            <div class="space-y-0.5">
                                <template x-if="tempAddress.latitude && tempAddress.longitude">
                                    <p class="text-stone-700">
                                        📍 Koordinat: <span class="font-mono font-semibold text-stone-900" x-text="Number(tempAddress.latitude).toFixed(6) + ', ' + Number(tempAddress.longitude).toFixed(6)"></span>
                                    </p>
                                </template>
                                <template x-if="pinAddressHint">
                                    <p class="text-[10px] text-stone-500 line-clamp-1 italic" x-text="'Patokan: ' + pinAddressHint"></p>
                                </template>
                            </div>
                            <button type="button" 
                                    x-show="tempAddress.latitude && tempAddress.longitude" 
                                    @click="clearCheckoutPin()" 
                                    class="text-xs font-medium text-rose-600 hover:underline">
                                Hapus Pin
                            </button>
                        </div>
                    </div>

                    <!-- Tandai Sebagai (Rumah / Kantor) ala Shopee -->
                    <div class="pt-1">
                        <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-stone-700">Tandai Sebagai</label>
                        <div class="flex items-center gap-2.5">
                            <button type="button" 
                                    @click="tempAddress.label = 'Rumah'" 
                                    :class="tempAddress.label === 'Rumah' ? 'border-[#201A17] bg-[#201A17] text-white shadow-xs font-bold' : 'border-stone-300 bg-white text-stone-700 hover:border-stone-400'"
                                    class="inline-flex items-center justify-center rounded-xl border px-4 py-2 text-xs font-semibold transition">
                                Rumah
                            </button>
                            <button type="button" 
                                    @click="tempAddress.label = 'Kantor'" 
                                    :class="tempAddress.label === 'Kantor' ? 'border-[#201A17] bg-[#201A17] text-white shadow-xs font-bold' : 'border-stone-300 bg-white text-stone-700 hover:border-stone-400'"
                                    class="inline-flex items-center justify-center rounded-xl border px-4 py-2 text-xs font-semibold transition">
                                Kantor
                            </button>
                        </div>
                    </div>

                    <div class="pt-1">
                        <label class="flex items-center gap-2.5 cursor-pointer">
                            <input type="checkbox" 
                                   x-model="tempAddress.isUtama"
                                   class="w-4 h-4 rounded text-[#B58742] focus:ring-[#B58742] border-stone-300">
                            <span class="text-xs font-semibold text-stone-700">Jadikan sebagai alamat utama</span>
                        </label>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="px-6 py-4 bg-stone-50 border-t border-stone-200 flex items-center justify-between">
                    <button x-show="modalView === 'list'" 
                            @click="editAddressModal = false" 
                            type="button"
                            class="px-4 py-2 text-xs font-semibold text-stone-600 hover:text-stone-900 transition">
                        Tutup
                    </button>
                    <button x-show="modalView === 'form'" 
                            @click="savedAddresses.length > 0 ? modalView = 'list' : editAddressModal = false" 
                            type="button"
                            class="px-4 py-2 text-xs font-semibold text-stone-600 hover:text-stone-900 transition">
                        Batal
                    </button>

                    <button x-show="modalView === 'form'" 
                            @click="saveAddress()" 
                            :disabled="modalSaving"
                            type="button"
                            class="px-6 py-2.5 text-xs font-bold text-white bg-[#201A17] hover:bg-stone-800 disabled:opacity-60 rounded-xl transition shadow-md flex items-center gap-2">
                        <svg x-show="modalSaving" class="animate-spin -ml-1 mr-1 h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                        <span x-text="modalSaving ? 'Menyimpan...' : 'Simpan & Gunakan'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ================= NAVBAR ================= -->
    <x-storefront-navbar />

    <!-- ================= MAIN CHECKOUT CONTENT ================= -->
    <main class="py-8 sm:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Page Header & Stepper matching Screenshot -->
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 pb-8 border-b border-[#ECE4D8]/80 mb-8 sm:mb-10">
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-widest text-[#B58742] block mb-1.5">
                        RINGKASAN PEMESANAN
                    </span>
                    <h1 class="font-serif-title text-3xl sm:text-4xl lg:text-5xl font-extrabold text-stone-900">
                        Selesaikan Pembayaran
                    </h1>
                </div>

                <!-- Stepper Progress Bar -->
                <div class="flex items-center gap-2 sm:gap-3 text-xs font-semibold">
                    <!-- Step 1: Keranjang (Completed) -->
                    <a href="{{ route('keranjang.index') }}" class="flex items-center gap-1.5 text-stone-500 hover:text-stone-800 transition">
                        <span class="w-5 h-5 rounded-full bg-stone-200 text-stone-700 flex items-center justify-center text-[10px] font-bold">✓</span>
                        <span>Keranjang</span>
                    </a>

                    <span class="w-6 h-px bg-stone-300"></span>

                    <!-- Step 2: Pengiriman & Pembayaran (Active) -->
                    <div class="flex items-center gap-1.5 text-stone-900 font-bold">
                        <span class="w-5 h-5 rounded-full bg-[#201A17] text-white flex items-center justify-center text-[10px]">2</span>
                        <span>Pengiriman & Pembayaran</span>
                    </div>

                    <span class="w-6 h-px bg-stone-300"></span>

                    <!-- Step 3: Selesai (Next) -->
                    <div class="flex items-center gap-1.5 text-stone-400">
                        <span class="w-5 h-5 rounded-full bg-stone-200 text-stone-400 flex items-center justify-center text-[10px]">3</span>
                        <span>Selesai</span>
                    </div>
                </div>
            </div>

            <!-- Main Layout Grid: Left (8 Cols) & Right (4 Cols) -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-10 items-start">
                
                <!-- Left Column: Address, Order Items, Shipping, Payment (8 Cols) -->
                <div class="lg:col-span-8 space-y-6">

                    <!-- ================= CARD 1: ALAMAT PENGIRIMAN ================= -->
                    <div class="bg-white rounded-3xl p-6 sm:p-7 border border-[#EDE6DB] shadow-sm">
                        <div class="flex items-center justify-between pb-4 border-b border-stone-100 mb-5">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-full bg-[#FAF4ED] text-[#9E6A38] flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <h2 class="font-bold text-base sm:text-lg text-stone-900 leading-tight">
                                        Alamat Pengiriman
                                    </h2>
                                    <p class="text-[11px] text-stone-500">Tujuan pengiriman pesanan Anda</p>
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                <button @click="openEditAddress()" 
                                        type="button"
                                        class="px-3.5 py-1.5 rounded-full border border-stone-300 hover:border-[#B58742] hover:text-[#B58742] text-xs font-semibold text-stone-700 bg-white shadow-2xs transition flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-stone-400 group-hover:text-[#B58742]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    <span>Ubah Alamat</span>
                                </button>
                            </div>
                        </div>

                        <!-- Address Info Box: data asli milik customer -->
                        <div x-show="addressData.address" class="p-4 sm:p-5 rounded-2xl bg-[#FAF7F2] border border-[#ECE4D8] space-y-2.5">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="px-2 py-0.5 rounded-md bg-[#201A17] text-[#E5C38E] text-[10px] font-bold uppercase tracking-wider" x-text="addressData.label || 'Rumah'"></span>
                                    <span class="font-bold text-stone-900 text-sm" x-text="addressData.recipient"></span>
                                    <span class="text-stone-300 font-normal">|</span>
                                    <span x-text="addressData.phone" class="font-semibold text-stone-700 text-xs"></span>
                                </div>

                                <div class="flex items-center gap-2">
                                    <span x-show="addressData.isUtama" class="px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-bold uppercase">
                                        Alamat Utama
                                    </span>
                                    <button @click="openAddAddress()" type="button" class="text-[11px] font-semibold text-[#B58742] hover:underline">
                                        + Tambah Baru
                                    </button>
                                </div>
                            </div>

                            <p class="text-xs sm:text-sm text-stone-700 leading-relaxed font-normal" x-text="fullAddress"></p>

                            <template x-if="addressData.latitude && addressData.longitude">
                                <div class="flex items-center gap-1.5 text-xs text-[#8C6226]">
                                    <svg class="h-3.5 w-3.5 shrink-0 text-[#B58742]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
                                    <span>Titik Maps Terpasang (<span class="font-mono" x-text="Number(addressData.latitude).toFixed(5) + ', ' + Number(addressData.longitude).toFixed(5)"></span>)</span>
                                    <a :href="'https://www.google.com/maps?q=' + addressData.latitude + ',' + addressData.longitude" target="_blank" rel="noopener noreferrer" class="ml-1 inline-flex items-center gap-0.5 font-bold text-stone-800 underline hover:text-[#B58742]">
                                        <span>Buka di Peta</span>
                                        <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/></svg>
                                    </a>
                                </div>
                            </template>

                            <div class="pt-1 flex flex-wrap items-center justify-between gap-2 border-t border-[#ECE4D8]/60 text-[11px] text-stone-500">
                                <span>Pastikan nomor WhatsApp / HP aktif untuk konfirmasi kurir pengiriman.</span>
                                <a href="{{ route('alamat.index') }}" class="font-bold text-stone-700 hover:text-stone-950 underline transition">
                                    Kelola alamat tersimpan →
                                </a>
                            </div>
                        </div>

                        <!-- Empty state: customer belum punya alamat -->
                        <div x-show="!addressData.address" class="p-6 rounded-2xl bg-amber-50/50 border border-dashed border-amber-300 text-center space-y-3">
                            <div class="w-10 h-10 rounded-full bg-amber-100 text-amber-800 mx-auto flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            </div>
                            <div>
                                <p class="font-bold text-sm text-stone-900">Belum Ada Alamat Pengiriman</p>
                                <p class="text-xs text-stone-500 mt-0.5">Silakan isi alamat tujuan Anda agar pesanan dapat diproses dan dikirimkan.</p>
                            </div>
                            <div class="pt-1">
                                <button @click="openAddAddress()" type="button" class="px-5 py-2.5 rounded-xl bg-[#201A17] hover:bg-stone-800 text-white text-xs font-bold transition shadow-sm inline-flex items-center gap-1.5">
                                    <span>+</span> Isi Alamat Pengiriman
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- ================= CARD 2: PRODUK YANG DIBELI (RINGKAS) ================= -->
                    <div class="bg-white rounded-3xl p-6 sm:p-7 border border-[#EDE6DB] shadow-sm">
                        <div class="flex items-center justify-between pb-4 border-b border-stone-100 mb-5">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-full bg-[#FAF4ED] text-[#9E6A38] flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25c-.669 0-1.189-.578-1.119-1.243l1.263-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                                    </svg>
                                </div>
                                <h2 class="font-bold text-base sm:text-lg text-stone-900">
                                    Produk yang Dibeli (Ringkas)
                                </h2>
                            </div>

                            <span class="text-xs font-semibold text-stone-500" x-text="items.length + ' Macam Koleksi'">
                                3 Macam Koleksi
                            </span>
                        </div>

                        <!-- Product Items List -->
                        <div class="divide-y divide-stone-100">
                            <template x-for="item in items" :key="item.id">
                                <div class="py-4 first:pt-0 last:pb-0 flex items-center justify-between gap-4">
                                    <div class="flex items-center gap-3.5 min-w-0">
                                        <img :src="item.gambar" 
                                             :alt="item.nama"
                                             class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl object-cover bg-stone-100 border border-stone-200 shrink-0">
                                        <div class="min-w-0 space-y-1">
                                            <span class="text-[11px] font-semibold text-stone-500 block truncate" x-text="item.varian"></span>
                                            <h3 class="font-bold text-sm sm:text-base text-stone-900 truncate" x-text="item.nama"></h3>
                                            <span class="text-xs text-stone-400 block" x-text="item.qty + 'x ' + (item.qty > 1 ? 'unit (' + formatRupiah(item.harga) + '/unit)' : 'potong')"></span>
                                        </div>
                                    </div>

                                    <div class="text-right shrink-0">
                                        <span class="font-serif-title font-bold text-base sm:text-lg text-stone-900" x-text="formatRupiah(item.harga * item.qty)"></span>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <!-- Catatan untuk Penjual -->
                        <div class="mt-6 pt-5 border-t border-stone-100">
                            <label class="block text-[11px] font-bold text-stone-800 uppercase tracking-wider mb-2">
                                Catatan untuk Penjual
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-stone-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487zm0 0L19.5 7.125"/>
                                    </svg>
                                </span>
                                <input type="text" 
                                       x-model="sellerNotes"
                                       maxlength="250"
                                       @input="sellerNotes = sellerNotes.replace(/[^a-zA-Z0-9\s.,\'\-()\/!?]/g, '')"
                                       placeholder="Contoh: Mohon sertakan kartu ucapan kado (maksimal 250 karakter)"
                                       class="w-full pl-10 pr-4 py-3 rounded-2xl border border-stone-200 text-xs sm:text-sm text-stone-800 bg-[#FAF7F2]/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#B58742]/40 focus:border-[#B58742] transition placeholder-stone-400">
                            </div>
                        </div>
                    </div>

                    <!-- ================= CARD 3: OPSI PENGIRIMAN (RAJAONGKIR) ================= -->
                    <div class="bg-white rounded-3xl p-6 sm:p-7 border border-[#EDE6DB] shadow-sm">
                        <div class="pb-4 border-b border-stone-100 mb-5">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-full bg-[#FAF4ED] text-[#9E6A38] flex items-center justify-center shrink-0">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.25V4.875A1.125 1.125 0 0013.125 3.75h-7.5A1.125 1.125 0 004.5 4.875v9.375m9 0V9.75" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h2 class="font-bold text-base sm:text-lg text-stone-900 flex items-center gap-2">
                                            <span>Opsi Pengiriman</span>
                                            <span class="text-[10px] uppercase font-bold tracking-wider px-2 py-0.5 rounded-full"
                                                  :class="isRajaOngkirLive ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800'"
                                                  x-text="isRajaOngkirLive ? 'RajaOngkir Live' : 'RajaOngkir Standar'"></span>
                                        </h2>
                                        <p class="text-xs text-stone-500 mt-0.5">
                                            Dari <span class="font-semibold text-stone-700">Surakarta (Solo)</span> &bull; Berat paket: <span class="font-semibold text-stone-700" x-text="totalWeightGrams + ' gr (~' + totalWeightKg + ' kg)'"></span>
                                        </p>
                                    </div>
                                </div>

                                <!-- Filter Tab Kurir -->
                                <div class="flex items-center gap-1 bg-stone-100 p-1 rounded-xl text-xs font-bold">
                                    <button type="button" @click="courierFilter = 'all'"
                                            :class="courierFilter === 'all' ? 'bg-white text-stone-900 shadow-2xs' : 'text-stone-500 hover:text-stone-800'"
                                            class="px-2.5 py-1 rounded-lg transition cursor-pointer">Semua</button>
                                    <button type="button" @click="courierFilter = 'JNE'"
                                            :class="courierFilter === 'JNE' ? 'bg-white text-stone-900 shadow-2xs' : 'text-stone-500 hover:text-stone-800'"
                                            class="px-2.5 py-1 rounded-lg transition cursor-pointer">JNE</button>
                                    <button type="button" @click="courierFilter = 'POS'"
                                            :class="courierFilter === 'POS' ? 'bg-white text-stone-900 shadow-2xs' : 'text-stone-500 hover:text-stone-800'"
                                            class="px-2.5 py-1 rounded-lg transition cursor-pointer">POS</button>
                                    <button type="button" @click="courierFilter = 'TIKI'"
                                            :class="courierFilter === 'TIKI' ? 'bg-white text-stone-900 shadow-2xs' : 'text-stone-500 hover:text-stone-800'"
                                            class="px-2.5 py-1 rounded-lg transition cursor-pointer">TIKI</button>
                                </div>
                            </div>
                        </div>

                        <!-- Loading State -->
                        <div x-show="loadingShipping" class="py-8 text-center text-xs text-stone-500 flex flex-col items-center justify-center gap-2">
                            <div class="w-6 h-6 border-2 border-stone-300 border-t-[#B58742] rounded-full animate-spin"></div>
                            <span>Menghitung ongkos kirim real-time...</span>
                        </div>

                        <!-- Courier Options Grid -->
                        <div x-show="!loadingShipping" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3.5">
                            <template x-for="service in filteredShippingServices" :key="service.code + '-' + service.service">
                                <div @click="selectedShippingKey = service.code + '-' + service.service"
                                     :class="(selectedShippingKey === (service.code + '-' + service.service))
                                         ? 'bg-[#201A17] text-white border-[#201A17] shadow-md ring-2 ring-[#B58742]/50' 
                                         : 'bg-[#FAF7F2] text-stone-800 border-stone-200 hover:border-stone-400'"
                                     class="rounded-2xl p-4 sm:p-5 border cursor-pointer transition-all duration-200 flex flex-col justify-between">
                                    <div>
                                        <div class="flex items-center justify-between mb-3">
                                            <div class="px-2.5 py-1 rounded-lg text-xs font-black tracking-wider uppercase"
                                                 :class="(selectedShippingKey === (service.code + '-' + service.service)) ? 'bg-stone-800 text-[#E5C38E]' : 'bg-white text-stone-800 shadow-2xs border border-stone-200/80'"
                                                 x-text="service.code">
                                            </div>
                                            <div class="w-4 h-4 rounded-full border-2 flex items-center justify-center"
                                                 :class="(selectedShippingKey === (service.code + '-' + service.service)) ? 'border-amber-400 bg-amber-400' : 'border-stone-300'">
                                                <div x-show="selectedShippingKey === (service.code + '-' + service.service)" class="w-1.5 h-1.5 rounded-full bg-[#201A17]"></div>
                                            </div>
                                        </div>
                                        <h3 class="font-bold text-sm" :class="(selectedShippingKey === (service.code + '-' + service.service)) ? 'text-white' : 'text-stone-900'">
                                            <span x-text="service.code + ' ' + service.service"></span>
                                        </h3>
                                        <p class="text-[11px] mt-0.5 leading-snug line-clamp-1" :class="(selectedShippingKey === (service.code + '-' + service.service)) ? 'text-stone-300' : 'text-stone-500'"
                                           x-text="service.description">
                                        </p>
                                        <p class="text-[11px] mt-1.5 font-medium" :class="(selectedShippingKey === (service.code + '-' + service.service)) ? 'text-amber-300' : 'text-stone-600'">
                                            Estimasi: <span class="font-bold" x-text="service.etd"></span>
                                        </p>
                                    </div>
                                    <div class="mt-4 pt-3 flex items-center justify-between border-t"
                                         :class="(selectedShippingKey === (service.code + '-' + service.service)) ? 'border-stone-700' : 'border-stone-200/60'">
                                        <span class="text-[9px] uppercase tracking-wider font-bold px-1.5 py-0.5 rounded"
                                              :class="(selectedShippingKey === (service.code + '-' + service.service)) ? 'bg-stone-800 text-stone-300' : 'bg-stone-200/60 text-stone-600'">
                                            TARIF
                                        </span>
                                        <span class="font-bold text-xs sm:text-sm" :class="(selectedShippingKey === (service.code + '-' + service.service)) ? 'text-white' : 'text-stone-900'"
                                              x-text="formatRupiah(service.cost)">
                                        </span>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- ================= CARD 4: METODE PEMBAYARAN ================= -->
                    <div class="bg-white rounded-3xl p-6 sm:p-7 border border-[#EDE6DB] shadow-sm">
                        <div class="flex items-center justify-between pb-4 border-b border-stone-100 mb-5">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-full bg-[#FAF4ED] text-[#9E6A38] flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" />
                                    </svg>
                                </div>
                                <h2 class="font-bold text-base sm:text-lg text-stone-900">
                                    Metode Pembayaran
                                </h2>
                            </div>

                            <div class="flex items-center gap-1.5 text-xs text-stone-500 font-semibold">
                                <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                                </svg>
                                <span>Enkripsi 256-bit</span>
                            </div>
                        </div>

                        <!-- Pembayaran tunggal via Midtrans (metode dipilih di popup Snap) -->
                        <div class="p-4 sm:p-5 rounded-2xl border border-[#B58742] bg-[#FAF7F2] ring-2 ring-[#B58742]/30 flex items-start gap-3">
                            <div class="mt-0.5 w-9 h-9 rounded-xl bg-[#201A17] text-[#E5C38E] flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" />
                                </svg>
                            </div>
                            <div class="flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="font-bold text-sm text-stone-900">Midtrans Payment Gateway</span>
                                    <span class="px-2 py-0.5 rounded-full bg-[#201A17] text-white text-[10px] font-bold">Otomatis</span>
                                </div>
                                <p class="text-xs text-stone-500 mt-1">
                                    Virtual Account, QRIS, E-Wallet, dan Kartu Kredit/Debit. Metode pembayaran dipilih pada jendela Midtrans setelah checkout — terverifikasi instan tanpa unggah bukti transfer.
                                </p>
                                <div class="hidden sm:flex items-center gap-1.5 mt-2.5">
                                    <span class="px-2 py-0.5 rounded bg-white text-[10px] font-extrabold text-blue-900 border border-stone-200">BCA</span>
                                    <span class="px-2 py-0.5 rounded bg-white text-[10px] font-extrabold text-amber-800 border border-stone-200">MANDIRI</span>
                                    <span class="px-2 py-0.5 rounded bg-white text-[10px] font-extrabold text-orange-700 border border-stone-200">BNI</span>
                                    <span class="px-2 py-0.5 rounded bg-white text-[10px] font-extrabold text-stone-700 border border-stone-200">QRIS</span>
                                    <span class="px-2 py-0.5 rounded bg-white text-[10px] font-extrabold text-stone-700 border border-stone-200">VISA</span>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Right Column: Sticky Ringkasan Pesanan (4 Cols) -->
                <div class="lg:col-span-4 sticky top-28 space-y-5">
                    
                    <!-- Ringkasan Belanja Card matching Mockup -->
                    <div class="bg-white rounded-3xl p-6 sm:p-7 border border-[#EDE6DB] shadow-md space-y-6">
                        
                        <div class="flex items-center justify-between pb-4 border-b border-stone-100">
                            <div>
                                <span class="text-[10px] font-bold uppercase tracking-wider text-stone-400 block mb-0.5">
                                    RINGKASAN BELANJA
                                </span>
                                <h2 class="font-bold text-stone-900 text-lg">
                                    Ringkasan Pesanan
                                </h2>
                            </div>
                            <a href="{{ route('keranjang.index') }}" 
                               class="p-2 text-stone-400 hover:text-stone-700 rounded-lg hover:bg-stone-100 transition"
                               title="Edit Isi Keranjang">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487zm0 0L19.5 7.125"/>
                                </svg>
                            </a>
                        </div>

                        <!-- Rows Breakdown matching Mockup exactly -->
                        <div class="space-y-3.5 text-xs sm:text-sm text-stone-600">
                            
                            <div class="flex items-center justify-between">
                                <span x-text="'Subtotal Produk (' + totalItemQty + ' Item)'">Subtotal Produk (4 Item)</span>
                                <span class="font-bold text-stone-900" x-text="formatRupiah(rawSubtotal)">Rp 900.000</span>
                            </div>

                            <div class="flex items-center justify-between">
                                <span class="flex items-center gap-1">
                                    <span>Total Ongkos Kirim</span>
                                    <svg class="w-3.5 h-3.5 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </span>
                                <span class="font-bold text-stone-900" x-text="formatRupiah(shippingCost)">Rp 20.000</span>
                            </div>

                        </div>

                        <!-- Divider with center dot -->
                        <div class="relative flex items-center justify-center my-2">
                            <div class="w-full border-t border-stone-200"></div>
                            <div class="absolute w-2 h-2 rounded-full bg-stone-300"></div>
                        </div>

                        <!-- Total Tagihan Section -->
                        <div class="flex items-baseline justify-between pt-1">
                            <div>
                                <span class="text-xs font-extrabold uppercase tracking-wider text-stone-800 block">
                                    TOTAL TAGIHAN
                                </span>
                                <span class="text-[11px] text-stone-400">
                                    Sudah termasuk PPN
                                </span>
                            </div>
                            <span class="font-serif-title text-2xl sm:text-3xl font-extrabold text-stone-900" 
                                  x-text="formatRupiah(grandTotal)">
                                Rp 871.000
                            </span>
                        </div>

                        <!-- Main Checkout CTA Button matching Mockup -->
                        <button @click="submitOrder()" 
                                :disabled="isSubmitting || items.length === 0"
                                class="w-full py-4 px-6 rounded-2xl bg-[#201A17] hover:bg-stone-800 disabled:opacity-60 text-white font-bold text-sm transition duration-200 shadow-md flex items-center justify-center gap-2 group cursor-pointer">
                            <span x-show="!isSubmitting" class="flex items-center gap-2">
                                <span>🔒</span>
                                <span>Bayar Sekarang</span>
                            </span>
                            <span x-show="isSubmitting" class="flex items-center gap-2">
                                <svg class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span>Memproses Transaksi...</span>
                            </span>
                        </button>

                        <!-- Legal & Terms Disclaimer -->
                        <p class="text-[11px] text-stone-500 text-center leading-relaxed px-2">
                            Dengan menekan Bayar Sekarang, Anda menyetujui Syarat & Ketentuan serta kebijakan pembelian toko Hamzah Style.
                        </p>

                    </div>

                    <!-- Butuh Bantuan Checkout? WhatsApp Card matching Mockup -->
                    <div class="bg-[#FAF7F2] rounded-3xl p-5 border border-[#EDE6DB] flex items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-2xl bg-white border border-stone-200 text-emerald-600 flex items-center justify-center shrink-0 shadow-2xs">
                                <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24">
                                    <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-bold text-xs sm:text-sm text-stone-900">
                                    Butuh Bantuan Checkout?
                                </h4>
                                <p class="text-[11px] text-stone-500 leading-tight">
                                    Layanan pendampingan pesanan anda via WhatsApp
                                </p>
                            </div>
                        </div>

                        <a href="https://wa.me/6281234567890?text=Halo%20Admin%20Hamzah%20Style,%20saya%20butuh%20bantuan%20untuk%20checkout%20pesanan"
                           target="_blank"
                           class="px-4 py-2 rounded-xl bg-white hover:bg-stone-50 border border-stone-200 text-stone-800 font-bold text-xs transition shadow-2xs shrink-0">
                            Chat
                        </a>
                    </div>

                </div>

            </div>

        </div>
    </main>

    <!-- ================= FOOTER ================= -->
    <x-storefront-footer />

</body>
</html>
