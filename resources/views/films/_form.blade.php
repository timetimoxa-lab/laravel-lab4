{{-- Общая форма для create и edit с мгновенной валидацией, фильтрацией спецсимволов, меню быстрого набора и защитой от дубликатов --}}
@php
    $f = $film ?? null;
    $isEdit = $isEdit ?? ($f !== null);
    $genresList = $genres ?? [];
    $rolesList = $roles ?? [];
    $existingFilmsList = $existingFilms ?? [];
    $releasedValue = old(
        'released_at',
        $f && $f->released_at ? $f->released_at->format('Y-m-d') : ''
    );
    $accentValue = old('accent_color', $f?->accent_color ?? '#3b4cca');
@endphp

@if ($errors->any())
    <div class="alert alert-danger" role="alert">
        <div class="fw-semibold mb-1">Проверьте заполнение полей:</div>
        <ul class="mb-0 ps-3">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<style>
    .combo-dropdown {
        max-height: 230px;
        overflow-y: auto;
        z-index: 1050;
    }
    .combo-dropdown .dropdown-item.active,
    .combo-dropdown .dropdown-item:active {
        background-color: #0d6efd;
        color: #fff;
    }
    .js-counter.counter-error {
        color: #dc3545 !important;
        font-weight: 500;
    }
    .js-counter.counter-ok {
        color: #198754 !important;
    }
    @keyframes shakeField {
        0%, 100% { transform: translateX(0); }
        25% { transform: translateX(-4px); }
        75% { transform: translateX(4px); }
    }
    .field-flash-error {
        animation: shakeField 0.22s ease-in-out;
        box-shadow: 0 0 0 0.25rem rgba(220, 53, 69, 0.28) !important;
        border-color: #dc3545 !important;
    }
    .dup-box {
        font-size: 0.875rem;
    }
    .dup-list-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem;
        padding: 0.25rem 0;
        border-bottom: 1px solid rgba(0, 0, 0, 0.06);
    }
    .dup-list-item:last-child {
        border-bottom: 0;
        padding-bottom: 0;
    }
</style>

{{-- Баннеры мгновенного предупреждения о дубликате и похожих карточках --}}
<div id="duplicate-alert" class="alert alert-danger d-none dup-box mb-3" role="alert"></div>
<div id="similar-alert" class="alert alert-warning d-none dup-box mb-3" role="status"></div>

{{-- Предсортированный индекс существующих фильмов для 0ms-проверки дубликатов --}}
<script id="existing-films-data" type="application/json">{!! json_encode($existingFilmsList, JSON_UNESCAPED_UNICODE) !!}</script>

<div class="row g-3">
    {{-- Название --}}
    <div class="col-12 col-md-6">
        <label for="title" class="form-label">Название <span class="text-danger">*</span></label>
        <input type="text"
               id="title"
               name="title"
               autocomplete="off"
               class="form-control js-validate @error('title') is-invalid @enderror"
               value="{{ old('title', $f?->title) }}"
               placeholder="Например: Сияние"
               data-required="true"
               data-min-chars="2"
               data-max-chars="150"
               data-check-dup="title"
               required>
        <div class="form-text js-counter" data-for="title"></div>
        <div class="invalid-feedback">@error('title'){{ $message }}@enderror</div>
    </div>

    {{-- Оригинальное название --}}
    <div class="col-12 col-md-6">
        <label for="original_title" class="form-label">Оригинальное название</label>
        <input type="text"
               id="original_title"
               name="original_title"
               autocomplete="off"
               class="form-control js-validate @error('original_title') is-invalid @enderror"
               value="{{ old('original_title', $f?->original_title) }}"
               placeholder="Например: The Shining"
               data-required="false"
               data-min-chars="2"
               data-max-chars="150"
               data-check-dup="original_title">
        <div class="form-text js-counter" data-for="original_title"></div>
        <div class="invalid-feedback">@error('original_title'){{ $message }}@enderror</div>
    </div>

    {{-- Роль: выбираемое меню с быстрым набором --}}
    <div class="col-12 col-md-6">
        <label for="role" class="form-label">Роль <span class="text-danger">*</span></label>
        <div class="combo-wrapper position-relative" data-combo>
            <div class="input-group has-validation">
                <input type="text"
                       id="role"
                       name="role"
                       autocomplete="off"
                       class="form-control js-validate combo-input @error('role') is-invalid @enderror"
                       value="{{ old('role', $f?->role) }}"
                       placeholder="Начните вводить или выберите роль..."
                       data-required="true"
                       data-min-chars="2"
                       data-max-chars="120"
                       required>
                <button type="button"
                        class="btn btn-outline-secondary combo-toggle"
                        aria-label="Открыть список ролей"
                        tabindex="-1">
                    <i class="fa-solid fa-chevron-down"></i>
                </button>
                <div class="invalid-feedback">@error('role'){{ $message }}@enderror</div>
            </div>
            <div class="dropdown-menu w-100 shadow-sm combo-dropdown"
                 data-options="{{ json_encode($rolesList, JSON_UNESCAPED_UNICODE) }}"></div>
        </div>
        <div class="form-text">Выберите роль из меню или начните вводить для быстрого поиска.</div>
    </div>

    {{-- Жанр: выбираемое меню с быстрым набором --}}
    <div class="col-12 col-md-6">
        <label for="genre" class="form-label">Жанр <span class="text-danger">*</span></label>
        <div class="combo-wrapper position-relative" data-combo>
            <div class="input-group has-validation">
                <input type="text"
                       id="genre"
                       name="genre"
                       autocomplete="off"
                       class="form-control js-validate combo-input @error('genre') is-invalid @enderror"
                       value="{{ old('genre', $f?->genre) }}"
                       placeholder="Начните вводить или выберите жанр..."
                       data-required="true"
                       data-min-chars="2"
                       data-max-chars="100"
                       required>
                <button type="button"
                        class="btn btn-outline-secondary combo-toggle"
                        aria-label="Открыть список жанров"
                        tabindex="-1">
                    <i class="fa-solid fa-chevron-down"></i>
                </button>
                <div class="invalid-feedback">@error('genre'){{ $message }}@enderror</div>
            </div>
            <div class="dropdown-menu w-100 shadow-sm combo-dropdown"
                 data-options="{{ json_encode($genresList, JSON_UNESCAPED_UNICODE) }}"></div>
        </div>
        <div class="form-text">Выберите вариант из списка или введите свой с клавиатуры.</div>
    </div>

    {{-- Дата выхода --}}
    <div class="col-12 col-md-6">
        <label for="released_at" class="form-label">Дата выхода <span class="text-danger">*</span></label>
        <input type="date"
               id="released_at"
               name="released_at"
               class="form-control js-validate-date @error('released_at') is-invalid @enderror"
               value="{{ $releasedValue }}"
               min="1900-01-01"
               max="{{ now()->format('Y-m-d') }}"
               required>
        <div class="invalid-feedback">@error('released_at'){{ $message }}@enderror</div>
    </div>

    {{-- Цвет карточки --}}
    <div class="col-12 col-md-6">
        <label for="accent_color" class="form-label">Цвет карточки <span class="text-danger">*</span></label>
        <div class="d-flex align-items-center gap-2">
            <input type="color"
                   id="accent_color"
                   name="accent_color"
                   class="form-control form-control-color @error('accent_color') is-invalid @enderror"
                   value="{{ $accentValue }}"
                   title="Выберите цвет карточки"
                   required>
            <code id="accent_color_hex" class="text-secondary">{{ $accentValue }}</code>
        </div>
        <div class="invalid-feedback d-block">@error('accent_color'){{ $message }}@enderror</div>
    </div>

    {{-- Краткое описание --}}
    <div class="col-12">
        <label for="short_description" class="form-label">Краткое описание (для карточки) <span class="text-danger">*</span></label>
        <textarea id="short_description"
                  name="short_description"
                  rows="2"
                  class="form-control js-validate @error('short_description') is-invalid @enderror"
                  placeholder="Кратко опишите сюжет (от 20 символов и не менее 4 слов)..."
                  data-required="true"
                  data-min-chars="20"
                  data-max-chars="300"
                  data-min-words="4"
                  required>{{ old('short_description', $f?->short_description) }}</textarea>
        <div class="form-text js-counter" data-for="short_description"></div>
        <div class="invalid-feedback">@error('short_description'){{ $message }}@enderror</div>
    </div>

    {{-- Описание --}}
    <div class="col-12">
        <label for="description" class="form-label">Описание <span class="text-danger">*</span></label>
        <textarea id="description"
                  name="description"
                  rows="5"
                  class="form-control js-validate @error('description') is-invalid @enderror"
                  placeholder="Подробное описание фильма и роли (от 50 символов и не менее 10 слов)..."
                  data-required="true"
                  data-min-chars="50"
                  data-max-chars="3000"
                  data-min-words="10"
                  required>{{ old('description', $f?->description) }}</textarea>
        <div class="form-text js-counter" data-for="description"></div>
        <div class="invalid-feedback">@error('description'){{ $message }}@enderror</div>
    </div>

    {{-- Постер --}}
    <div class="col-12">
        <label for="poster" class="form-label">
            Постер (JPG/PNG, до 4 МБ; будет приведён к 400×600)
            @if (! $isEdit)<span class="text-danger">*</span>@endif
        </label>
        @if ($isEdit && $f && $f->poster_path)
            <div class="mb-2 d-flex align-items-center gap-3">
                <img src="{{ $f->poster_url }}" alt="Текущий постер"
                     class="rounded border" style="height: 110px; width: auto;">
                <span class="small text-secondary">Текущий постер. Выберите файл, только если хотите его заменить.</span>
            </div>
        @endif
        <input type="file"
               id="poster"
               name="poster"
               accept=".jpg,.jpeg,.png,image/jpeg,image/png"
               class="form-control js-validate-file @error('poster') is-invalid @enderror"
               data-required="{{ $isEdit ? 'false' : 'true' }}"
               @if (! $isEdit) required @endif>
        <div class="invalid-feedback">@error('poster'){{ $message }}@enderror</div>
    </div>
</div>

<div class="d-flex flex-wrap align-items-center gap-2 mt-4">
    <button type="submit" id="film-submit-btn" class="btn btn-primary">
        <i class="fa-solid fa-floppy-disk me-1"></i>{{ $submitLabel ?? 'Сохранить' }}
    </button>
    <a href="{{ $isEdit && $f ? route('films.show', $f) : route('films.index') }}"
       class="btn btn-outline-secondary">Отмена</a>
    <span id="submit-lock-hint" class="small text-danger d-none ms-2">
        <i class="fa-solid fa-ban me-1"></i>Исправьте дубликат, чтобы сохранить карточку
    </span>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('film-form');
    if (!form) return;

    const submitBtn = document.getElementById('film-submit-btn');
    const submitLockHint = document.getElementById('submit-lock-hint');
    const dupAlert = document.getElementById('duplicate-alert');
    const similarAlert = document.getElementById('similar-alert');
    const titleInput = document.getElementById('title');
    const origInput = document.getElementById('original_title');

    // Предзагруженный отсортированный каталог для 0ms-проверки дубликатов
    let existingFilms = [];
    const dataNode = document.getElementById('existing-films-data');
    if (dataNode) {
        try {
            existingFilms = JSON.parse(dataNode.textContent || '[]');
        } catch {
            existingFilms = [];
        }
    }

    existingFilms.sort((a, b) => (a.title || '').localeCompare(b.title || '', 'ru'));

    const checkUrl = form.dataset.checkUrl || '';
    const ignoreId = form.dataset.ignoreId || '';

    // --------------------------------------------------------------
    // 1. Синхронизация отображения HEX-кода цвета
    // --------------------------------------------------------------
    const colorInput = document.getElementById('accent_color');
    const colorHex = document.getElementById('accent_color_hex');
    if (colorInput && colorHex) {
        colorInput.addEventListener('input', () => {
            colorHex.textContent = colorInput.value;
        });
    }

    // --------------------------------------------------------------
    // 2. Утилиты нормализации, подсчёта и фильтрации спецсимволов В МОМЕНТЕ
    //    Символы @ # $ % & разрешены внутри осмысленного текста,
    //    опасные (< > \ / ^ * _ = + ~ | [ ] { }) вырезаются сразу при вводе.
    // --------------------------------------------------------------
    const forbiddenCharsGlobal = /[^\p{L}\p{N}\s\-\.,:;«»"'()!?—–@#$%&]/gu;
    const repeatedPunctRegex = /([!?,\-:;@#$%&])\1{1,}/u;
    const alnumRegex = /[\p{L}\p{N}]/gu;
    const wordRegex = /[\p{L}\p{N}]+/gu;

    const duplicateState = {
        title: null,
        original_title: null,
    };

    const flashTimers = new WeakMap();

    function normalizeKey(str) {
        return (str || '')
            .trim()
            .toLowerCase()
            .replace(/ё/g, 'е')
            .replace(/[^\p{L}\p{N}]+/gu, ' ')
            .replace(/\s+/g, ' ')
            .trim();
    }

    function escapeHtml(str) {
        return String(str ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function levenshtein(a, b) {
        if (a === b) return 0;
        if (!a.length) return b.length;
        if (!b.length) return a.length;
        const prev = Array.from({ length: b.length + 1 }, (_, i) => i);
        const curr = new Array(b.length + 1);
        for (let i = 1; i <= a.length; i++) {
            curr[0] = i;
            for (let j = 1; j <= b.length; j++) {
                const cost = a[i - 1] === b[j - 1] ? 0 : 1;
                curr[j] = Math.min(prev[j] + 1, curr[j - 1] + 1, prev[j - 1] + cost);
            }
            for (let j = 0; j <= b.length; j++) prev[j] = curr[j];
        }
        return prev[b.length];
    }

    function countWords(text) {
        const matches = text.match(wordRegex);
        return matches ? matches.length : 0;
    }

    function countAlnum(text) {
        const matches = text.match(alnumRegex);
        return matches ? matches.length : 0;
    }

    function getFeedbackEl(field) {
        const group = field.closest('.input-group');
        if (group) {
            return group.querySelector('.invalid-feedback');
        }
        return field.parentElement.querySelector('.invalid-feedback');
    }

    function setFieldState(field, errorMessage) {
        const feedback = getFeedbackEl(field);
        if (errorMessage) {
            field.classList.add('is-invalid');
            field.classList.remove('is-valid');
            if (feedback) feedback.textContent = errorMessage;
            return false;
        }
        field.classList.remove('is-invalid');
        if (field.value.trim() !== '' || field.dataset.required === 'true') {
            field.classList.add('is-valid');
        } else {
            field.classList.remove('is-valid');
        }
        if (feedback) feedback.textContent = '';
        return true;
    }

    function triggerShake(field) {
        field.classList.remove('field-flash-error');
        void field.offsetWidth;
        field.classList.add('field-flash-error');

        if (flashTimers.has(field)) {
            clearTimeout(flashTimers.get(field));
        }
        const timer = setTimeout(() => {
            field.classList.remove('field-flash-error');
        }, 500);
        flashTimers.set(field, timer);
    }

    function flashForbiddenWarning(field, blockedChars) {
        const uniqueBlocked = Array.from(new Set(blockedChars)).join(' ');
        const feedback = getFeedbackEl(field);

        field.classList.remove('field-flash-error');
        void field.offsetWidth;
        field.classList.add('field-flash-error', 'is-invalid');
        field.classList.remove('is-valid');

        if (feedback) {
            feedback.textContent = `Технический символ «${uniqueBlocked}» запрещён и удалён автоматически в моменте.`;
        }

        if (flashTimers.has(field)) {
            clearTimeout(flashTimers.get(field));
        }
        const timer = setTimeout(() => {
            field.classList.remove('field-flash-error');
            validateTextField(field);
        }, 2200);
        flashTimers.set(field, timer);
    }

    /**
     * Борьба с запрещёнными спецсимволами прямо в момент набора/вставки:
     * опасные символы сразу вырезаются, курсор сохраняется, поле потряхивается и подсвечивается красным.
     */
    function filterSpecialCharsLive(field) {
        const raw = field.value;
        const found = raw.match(forbiddenCharsGlobal);
        if (!found) return false;

        const start = field.selectionStart ?? raw.length;
        const beforeCursor = raw.slice(0, start);
        const removedBeforeCursor = (beforeCursor.match(forbiddenCharsGlobal) || []).length;

        field.value = raw.replace(forbiddenCharsGlobal, '');
        const nextPos = Math.max(0, start - removedBeforeCursor);
        if (typeof field.setSelectionRange === 'function') {
            field.setSelectionRange(nextPos, nextPos);
        }

        flashForbiddenWarning(field, found);
        return true;
    }

    function updateCounter(field) {
        const counter = form.querySelector(`.js-counter[data-for="${field.id}"]`);
        if (!counter) return;

        const val = field.value.trim();
        const minChars = Number(field.dataset.minChars || 0);
        const maxChars = Number(field.dataset.maxChars || 0);
        const minWords = Number(field.dataset.minWords || 0);
        const isReq = field.dataset.required === 'true';

        if (!isReq && val === '') {
            counter.textContent = `Необязательно (от ${minChars} до ${maxChars} симв.)`;
            counter.classList.remove('counter-error', 'counter-ok');
            return;
        }

        const chars = val.length;
        const words = countWords(val);
        const parts = [`Символов: ${chars} (от ${minChars} до ${maxChars})`];
        if (minWords > 0) {
            parts.push(`Слов: ${words} (мин. ${minWords})`);
        }
        counter.textContent = parts.join(' · ');

        const bad = chars < minChars || chars > maxChars || (minWords > 0 && words < minWords);
        counter.classList.toggle('counter-error', bad && chars > 0);
        counter.classList.toggle('counter-ok', !bad && chars > 0);
    }

    function validateTextField(field, shakeOnSpecialOnly = false) {
        const val = field.value.trim();
        const isReq = field.dataset.required === 'true';
        const minChars = Number(field.dataset.minChars || 0);
        const maxChars = Number(field.dataset.maxChars || 0);
        const minWords = Number(field.dataset.minWords || 0);
        const dupKind = field.dataset.checkDup || '';

        if (val === '') {
            if (isReq) {
                return setFieldState(field, 'Это поле обязательно для заполнения.');
            }
            return setFieldState(field, '');
        }

        if (forbiddenCharsGlobal.test(val)) {
            forbiddenCharsGlobal.lastIndex = 0;
            return setFieldState(field, 'Запрещены технические символы (<, >, \\, /, ^, *, _, =, +, ~, |, [, ], {, }).');
        }
        forbiddenCharsGlobal.lastIndex = 0;

        if (repeatedPunctRegex.test(val)) {
            if (shakeOnSpecialOnly) triggerShake(field);
            return setFieldState(field, 'Не допускаются повторяющиеся подряд спецсимволы (например, «!!», «??», «--», «@@», «##»).');
        }

        if (countAlnum(val) < 2) {
            if (shakeOnSpecialOnly && countAlnum(val) === 0) triggerShake(field);
            return setFieldState(
                field,
                'Специальные символы не могут быть единственным содержимым или одним символом — введите минимум 2 буквы или цифры.'
            );
        }

        if (minChars && val.length < minChars) {
            return setFieldState(field, `Слишком короткий текст: минимум ${minChars} симв. (сейчас ${val.length}).`);
        }

        if (maxChars && val.length > maxChars) {
            return setFieldState(field, `Превышен лимит: максимум ${maxChars} симв. (сейчас ${val.length}).`);
        }

        if (minWords) {
            const words = countWords(val);
            if (words < minWords) {
                return setFieldState(field, `Слишком мало слов: минимум ${minWords} (сейчас ${words}).`);
            }
        }

        if (dupKind && duplicateState[dupKind]) {
            const d = duplicateState[dupKind];
            const yr = d.year ? ` (${d.year})` : '';
            const owner = d.owner ? ` · @${d.owner}` : '';
            return setFieldState(field, `Дубликат! Фильм «${d.title}»${yr}${owner} уже существует в базе.`);
        }

        return setFieldState(field, '');
    }

    function validateDateField(field) {
        if (!field) return true;
        const val = field.value;
        if (!val) {
            return setFieldState(field, 'Укажите дату выхода фильма.');
        }
        if (field.min && val < field.min) {
            return setFieldState(field, 'Дата не может быть раньше 01.01.1900.');
        }
        if (field.max && val > field.max) {
            return setFieldState(field, 'Дата не может быть в будущем.');
        }
        return setFieldState(field, '');
    }

    function validateFileField(field) {
        if (!field) return true;
        const isReq = field.dataset.required === 'true';
        const file = field.files && field.files[0];

        if (!file) {
            if (isReq) {
                return setFieldState(field, 'Загрузите изображение постера (JPG или PNG).');
            }
            field.classList.remove('is-invalid', 'is-valid');
            const fb = getFeedbackEl(field);
            if (fb) fb.textContent = '';
            return true;
        }

        const allowedTypes = ['image/jpeg', 'image/png'];
        const ext = file.name.split('.').pop().toLowerCase();
        if (!allowedTypes.includes(file.type) && !['jpg', 'jpeg', 'png'].includes(ext)) {
            return setFieldState(field, 'Допустимы только файлы JPG, JPEG или PNG.');
        }

        if (file.size > 4 * 1024 * 1024) {
            return setFieldState(field, 'Размер постера не должен превышать 4 МБ.');
        }

        return setFieldState(field, '');
    }

    // --------------------------------------------------------------
    // 3. Мгновенная борьба с дубликатами + сортировка в моменте (0ms + AJAX)
    // --------------------------------------------------------------
    function updateSubmitLock() {
        const hasDup = Boolean(duplicateState.title || duplicateState.original_title);
        if (submitBtn) {
            submitBtn.disabled = hasDup;
        }
        if (submitLockHint) {
            submitLockHint.classList.toggle('d-none', !hasDup);
        }
    }

    function renderDuplicateBanners(exactTitle, exactOrig, similarList) {
        duplicateState.title = exactTitle || null;
        duplicateState.original_title = exactOrig || null;

        const exactMatches = [];
        if (exactTitle) exactMatches.push({ label: 'Название', item: exactTitle });
        if (exactOrig && (!exactTitle || exactOrig.id !== exactTitle.id)) {
            exactMatches.push({ label: 'Оригинальное название', item: exactOrig });
        }

        if (dupAlert) {
            if (exactMatches.length > 0) {
                const rows = exactMatches.map(({ label, item }) => {
                    const yr = item.year ? ` (${item.year})` : '';
                    const owner = item.owner ? ` · @${item.owner}` : '';
                    return `
                        <div class="dup-list-item">
                            <span>
                                <strong>${escapeHtml(label)}:</strong>
                                «${escapeHtml(item.title)}»${escapeHtml(yr)} — ${escapeHtml(item.genre || '')}${escapeHtml(owner)}
                            </span>
                            <a href="${escapeHtml(item.url)}" target="_blank" class="btn btn-sm btn-outline-danger py-0">
                                Открыть карточку
                            </a>
                        </div>`;
                }).join('');
                dupAlert.innerHTML = `
                    <div class="fw-semibold mb-1">
                        <i class="fa-solid fa-triangle-exclamation me-1"></i>
                        Обнаружен дубликат! Такая карточка уже есть в базе — создание заблокировано:
                    </div>
                    ${rows}`;
                dupAlert.classList.remove('d-none');
            } else {
                dupAlert.classList.add('d-none');
                dupAlert.innerHTML = '';
            }
        }

        const exactIds = new Set(exactMatches.map((m) => m.item.id));
        const filteredSimilar = (similarList || [])
            .filter((item) => !exactIds.has(item.id))
            .sort((a, b) => (a.title || '').localeCompare(b.title || '', 'ru'));

        if (similarAlert) {
            if (filteredSimilar.length > 0) {
                const rows = filteredSimilar.map((item) => {
                    const yr = item.year ? ` (${item.year})` : '';
                    const orig = item.original_title ? ` / ${item.original_title}` : '';
                    const owner = item.owner ? ` · @${item.owner}` : '';
                    return `
                        <div class="dup-list-item">
                            <span>
                                <strong>${escapeHtml(item.title)}</strong>${escapeHtml(yr)}
                                <span class="text-secondary">${escapeHtml(orig)}${escapeHtml(owner)}</span>
                            </span>
                            <a href="${escapeHtml(item.url)}" target="_blank" class="btn btn-sm btn-outline-dark py-0">
                                Проверить
                            </a>
                        </div>`;
                }).join('');
                similarAlert.innerHTML = `
                    <div class="fw-semibold mb-1">
                        <i class="fa-solid fa-magnifying-glass me-1"></i>
                        Похожие фильмы уже в базе (отсортировано по алфавиту):
                    </div>
                    ${rows}`;
                similarAlert.classList.remove('d-none');
            } else {
                similarAlert.classList.add('d-none');
                similarAlert.innerHTML = '';
            }
        }

        if (titleInput && titleInput.value.trim() !== '') {
            validateTextField(titleInput);
        }
        if (origInput && origInput.value.trim() !== '') {
            validateTextField(origInput);
        }
        updateSubmitLock();
    }

    function checkDuplicatesLocal() {
        const rawTitle = titleInput ? titleInput.value : '';
        const rawOrig = origInput ? origInput.value : '';

        const normTitle = normalizeKey(rawTitle);
        const compactTitle = normTitle.replace(/\s+/g, '');

        const normOrig = normalizeKey(rawOrig);
        const compactOrig = normOrig.replace(/\s+/g, '');

        let exactTitle = null;
        let exactOrig = null;
        const similar = [];

        for (const f of existingFilms) {
            const fNormTitle = f.norm_title || normalizeKey(f.title);
            const fCompactTitle = fNormTitle.replace(/\s+/g, '');
            const fNormOrig = f.norm_orig || normalizeKey(f.original_title);
            const fCompactOrig = fNormOrig.replace(/\s+/g, '');

            if (normTitle !== '' && (
                fNormTitle === normTitle
                || (compactTitle !== '' && fCompactTitle === compactTitle)
                || (fNormOrig !== '' && fNormOrig === normTitle)
            )) {
                exactTitle = f;
            }

            if (normOrig !== '' && (
                (fNormOrig !== '' && (fNormOrig === normOrig || (compactOrig !== '' && fCompactOrig === compactOrig)))
                || fNormTitle === normOrig
            )) {
                exactOrig = f;
            }

            let isSim = false;
            if (normTitle.length >= 2) {
                if (fNormTitle.includes(normTitle) || normTitle.includes(fNormTitle)) {
                    isSim = true;
                } else if (normTitle.length >= 4 && Math.abs(normTitle.length - fNormTitle.length) <= 2) {
                    const dist = levenshtein(normTitle, fNormTitle);
                    if (dist <= 2) isSim = true;
                }
            }
            if (!isSim && normOrig.length >= 2 && fNormOrig) {
                if (fNormOrig.includes(normOrig) || normOrig.includes(fNormOrig)) {
                    isSim = true;
                }
            }

            if (isSim) {
                similar.push(f);
            }
        }

        renderDuplicateBanners(exactTitle, exactOrig, similar.slice(0, 8));
    }

    let ajaxTimer = null;
    let ajaxSeq = 0;

    function scheduleServerDuplicateCheck() {
        if (!checkUrl) return;
        clearTimeout(ajaxTimer);
        ajaxTimer = setTimeout(async () => {
            const seq = ++ajaxSeq;
            const params = new URLSearchParams({
                title: titleInput ? titleInput.value : '',
                original_title: origInput ? origInput.value : '',
            });
            if (ignoreId) params.set('ignore_id', ignoreId);

            try {
                const res = await fetch(`${checkUrl}?${params.toString()}`, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });
                if (!res.ok || seq !== ajaxSeq) return;
                const data = await res.json();
                renderDuplicateBanners(data.exact_title, data.exact_original, data.similar);
            } catch {
                // Локальная 0ms-проверка уже сработала
            }
        }, 220);
    }

    // --------------------------------------------------------------
    // 4. Подключение слушателей ко всем полям (всё работает в моменте)
    // --------------------------------------------------------------
    const textFields = form.querySelectorAll('.js-validate');
    const dateField = form.querySelector('.js-validate-date');
    const fileField = form.querySelector('.js-validate-file');

    textFields.forEach((field) => {
        updateCounter(field);

        field.addEventListener('input', () => {
            const stripped = filterSpecialCharsLive(field);
            updateCounter(field);

            if (field.dataset.checkDup) {
                checkDuplicatesLocal();
                scheduleServerDuplicateCheck();
            }

            if (!stripped) {
                validateTextField(field, true);
            }
        });

        field.addEventListener('blur', () => {
            if (field.dataset.checkDup) {
                checkDuplicatesLocal();
            }
            validateTextField(field, false);
        });
    });

    if (dateField) {
        dateField.addEventListener('change', () => validateDateField(dateField));
        dateField.addEventListener('blur', () => validateDateField(dateField));
    }

    if (fileField) {
        fileField.addEventListener('change', () => validateFileField(fileField));
    }

    if ((titleInput && titleInput.value.trim()) || (origInput && origInput.value.trim())) {
        checkDuplicatesLocal();
    }

    // --------------------------------------------------------------
    // 5. Выбираемое меню с быстрым набором (Роль и Жанр)
    // --------------------------------------------------------------
    const combos = form.querySelectorAll('[data-combo]');

    combos.forEach((wrapper) => {
        const input = wrapper.querySelector('.combo-input');
        const toggleBtn = wrapper.querySelector('.combo-toggle');
        const menu = wrapper.querySelector('.combo-dropdown');
        if (!input || !menu) return;

        let options = [];
        try {
            options = JSON.parse(menu.dataset.options || '[]');
        } catch {
            options = [];
        }
        options.sort((a, b) => a.localeCompare(b, 'ru'));

        let activeIndex = -1;

        function renderOptions(query) {
            const q = query.trim().toLowerCase();
            const filtered = q === ''
                ? options
                : options.filter((opt) => opt.toLowerCase().includes(q));

            menu.innerHTML = '';
            activeIndex = -1;

            if (filtered.length === 0) {
                const empty = document.createElement('div');
                empty.className = 'dropdown-item-text small text-secondary';
                empty.textContent = q ? `Будет использован свой вариант: «${query.trim()}»` : 'Список пуст';
                menu.appendChild(empty);
                return;
            }

            filtered.forEach((opt) => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'dropdown-item';
                if (opt.toLowerCase() === q) {
                    btn.classList.add('fw-semibold');
                }
                btn.textContent = opt;
                btn.addEventListener('mousedown', (e) => {
                    e.preventDefault();
                    selectOption(opt);
                });
                menu.appendChild(btn);
            });
        }

        function openMenu(showAll = false) {
            renderOptions(showAll ? '' : input.value);
            menu.classList.add('show');
        }

        function closeMenu() {
            menu.classList.remove('show');
            activeIndex = -1;
        }

        function selectOption(value) {
            input.value = value;
            closeMenu();
            validateTextField(input);
            input.focus();
        }

        function updateActiveItem(items) {
            items.forEach((el, idx) => {
                el.classList.toggle('active', idx === activeIndex);
                if (idx === activeIndex) {
                    el.scrollIntoView({ block: 'nearest' });
                }
            });
        }

        input.addEventListener('focus', () => openMenu(false));
        input.addEventListener('input', () => openMenu(false));

        input.addEventListener('keydown', (e) => {
            const items = menu.querySelectorAll('button.dropdown-item');
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                if (!menu.classList.contains('show')) openMenu(false);
                if (items.length > 0) {
                    activeIndex = (activeIndex + 1) % items.length;
                    updateActiveItem(items);
                }
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                if (items.length > 0) {
                    activeIndex = (activeIndex - 1 + items.length) % items.length;
                    updateActiveItem(items);
                }
            } else if (e.key === 'Enter' && menu.classList.contains('show') && activeIndex >= 0 && items[activeIndex]) {
                e.preventDefault();
                selectOption(items[activeIndex].textContent);
            } else if (e.key === 'Escape') {
                closeMenu();
            }
        });

        if (toggleBtn) {
            toggleBtn.addEventListener('mousedown', (e) => {
                e.preventDefault();
                if (menu.classList.contains('show')) {
                    closeMenu();
                } else {
                    input.focus();
                    openMenu(true);
                }
            });
        }

        document.addEventListener('click', (e) => {
            if (!wrapper.contains(e.target)) {
                closeMenu();
            }
        });
    });

    // --------------------------------------------------------------
    // 6. Финальная проверка перед отправкой формы
    // --------------------------------------------------------------
    form.addEventListener('submit', (e) => {
        checkDuplicatesLocal();

        let valid = true;
        textFields.forEach((field) => {
            if (!validateTextField(field)) valid = false;
        });
        if (!validateDateField(dateField)) valid = false;
        if (!validateFileField(fileField)) valid = false;
        if (duplicateState.title || duplicateState.original_title) valid = false;

        if (!valid) {
            e.preventDefault();
            const firstInvalid = form.querySelector('.is-invalid');
            if (firstInvalid) {
                firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                firstInvalid.focus();
            }
        }
    });
});
</script>
