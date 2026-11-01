@extends('layouts.main')

@section('content')
<style>
    .ireg { background: #f6f3ee; padding: 40px 0 60px; }
    .ireg-side { background: #1e2a38; color: #fff; border-radius: 12px; padding: 36px 30px; height: 100%; }
    .ireg-side .eyebrow { color: #e0705a; font-size: 12px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; }
    .ireg-side h2 { color: #fff; font-size: 34px; line-height: 1.2; margin: 14px 0 18px; }
    .ireg-side p, .ireg-side li { color: #c9d1da; }
    .ireg-side ul { padding-left: 18px; margin-top: 20px; }
    .ireg-side li { margin-bottom: 10px; }
    .ireg-card { background: #fff; border: 1px solid #ece8e1; border-radius: 14px; padding: 26px 24px 8px; margin-bottom: 22px; }
    .ireg-card h6 { color: #e0705a; font-size: 12px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; margin-bottom: 18px; }
    .ireg label { font-weight: 600; font-size: 13px; color: #1e2a38; margin-bottom: 6px; }
    .ireg .form-control { height: 46px; border-radius: 10px; }
    .ireg .form-control:disabled { background: #eceef1; }
    .ireg .invalid-feedback { display: block; }
    .ireg .btn-register { background: #e0705a; border: 0; color: #fff; font-weight: 600; width: 100%; padding: 14px; border-radius: 10px; font-size: 16px; }
    .ireg .btn-register:hover { background: #cf5f49; }
    .ireg .price-hint { font-size: 13px; color: #6b7785; margin-top: 6px; }
</style>

<div class="ireg">
    <div class="container">
        <div class="row">
            <div class="col-lg-4 mb-4 mb-lg-0">
                <div class="ireg-side">
                    <div class="eyebrow">Student registration</div>
                    <h2>Register for your UGC internship.</h2>
                    <p>Fill in your personal and academic details, then complete secure payment to activate your internship.</p>
                    <ul>
                        <li>Instant account setup</li>
                        <li>University-wise college selection</li>
                        <li>Certificate of internship on completion</li>
                        <li>Secure payment before course access</li>
                    </ul>
                </div>
            </div>

            <div class="col-lg-8">
                @if($errors->any())
                    <div class="alert alert-danger" role="alert">
                        <ul class="mb-0 pl-3">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <h3 class="mb-3">Student Registration</h3>
                <p class="mb-4">Already registered? <a href="{{ route('login') }}">Log in</a> to continue your payment.</p>

                <form id="internshipForm" method="POST" action="{{ route('internship.register.store') }}" novalidate>
                    @csrf

                    <div class="ireg-card">
                        <h6>Personal Details</h6>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="name">Full Name</label>
                                <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" maxlength="255" required autocomplete="name">
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="email">Email Address</label>
                                <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" maxlength="255" required autocomplete="email">
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="mobile">Mobile Number</label>
                                <input type="tel" id="mobile" name="mobile" class="form-control @error('mobile') is-invalid @enderror" value="{{ old('mobile') }}" inputmode="numeric" maxlength="10" pattern="[6-9][0-9]{9}" title="10-digit Indian mobile number" required autocomplete="tel">
                                @error('mobile')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="whatsapp">WhatsApp Number</label>
                                <input type="tel" id="whatsapp" name="whatsapp" class="form-control @error('whatsapp') is-invalid @enderror" value="{{ old('whatsapp') }}" inputmode="numeric" maxlength="10" pattern="[6-9][0-9]{9}" title="10-digit Indian WhatsApp number" required>
                                <div class="form-check mt-1">
                                    <input type="checkbox" class="form-check-input" id="sameAsMobile">
                                    <label class="form-check-label" for="sameAsMobile" style="font-weight: 400;">Same as mobile number</label>
                                </div>
                                @error('whatsapp')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="guardian_name">Parent / Guardian Name</label>
                                <input type="text" id="guardian_name" name="guardian_name" class="form-control @error('guardian_name') is-invalid @enderror" value="{{ old('guardian_name') }}" maxlength="255" required>
                                @error('guardian_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="gender">Gender</label>
                                <select id="gender" name="gender" class="form-control @error('gender') is-invalid @enderror" required>
                                    <option value="">Select</option>
                                    @foreach($genders as $value => $label)
                                        <option value="{{ $value }}" {{ old('gender') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('gender')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="password">Create Password</label>
                            <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror" minlength="8" maxlength="255" required autocomplete="new-password">
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="ireg-card">
                        <h6>Academic Details</h6>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="university_id">University</label>
                                <select id="university_id" name="university_id" class="form-control @error('university_id') is-invalid @enderror" required>
                                    <option value="">Select university</option>
                                    @foreach($universities as $university)
                                        <option value="{{ $university->id }}" {{ (string) old('university_id') === (string) $university->id ? 'selected' : '' }}>{{ $university->name }}</option>
                                    @endforeach
                                </select>
                                @error('university_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="college_id">College</label>
                                <select id="college_id" name="college_id" class="form-control @error('college_id') is-invalid @enderror" data-placeholder="Select college" data-disabled-placeholder="Select university first" required {{ old('university_id') ? '' : 'disabled' }}>
                                    <option value="">{{ old('university_id') ? 'Select college' : 'Select university first' }}</option>
                                    @foreach($colleges as $college)
                                        <option value="{{ $college->id }}" data-university="{{ $college->university_id }}" {{ (string) old('college_id') === (string) $college->id ? 'selected' : '' }}>{{ $college->name }}</option>
                                    @endforeach
                                </select>
                                @error('college_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="internship_id">Programme</label>
                                <select id="internship_id" name="internship_id" class="form-control @error('internship_id') is-invalid @enderror" data-placeholder="Select programme" data-disabled-placeholder="Select college first" required {{ old('college_id') ? '' : 'disabled' }}>
                                    <option value="">{{ old('college_id') ? 'Select programme' : 'Select college first' }}</option>
                                    @foreach($internships as $internship)
                                        <option value="{{ $internship->id }}" data-college="{{ $internship->college_id }}" {{ (string) old('internship_id') === (string) $internship->id ? 'selected' : '' }}
                                            data-price="{{ number_format($internship->price, 2) }}"
                                            data-tax="{{ rtrim(rtrim(number_format($internship->tax_percent, 2, '.', ''), '0'), '.') }}"
                                            data-total="{{ number_format($internship->getTotalAmount(), 2) }}">
                                            {{ $internship->name }}@if($internship->duration_weeks) ({{ $internship->duration_weeks }} weeks)@endif
                                        </option>
                                    @endforeach
                                </select>
                                <div class="price-hint" id="priceHint"></div>
                                @error('internship_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="stream_id">Stream</label>
                                <select id="stream_id" name="stream_id" class="form-control @error('stream_id') is-invalid @enderror" data-placeholder="Select stream" data-disabled-placeholder="Select college first" required {{ old('college_id') ? '' : 'disabled' }}>
                                    <option value="">{{ old('college_id') ? 'Select stream' : 'Select college first' }}</option>
                                    @foreach($streams as $stream)
                                        <option value="{{ $stream->id }}" {{ (string) old('stream_id') === (string) $stream->id ? 'selected' : '' }}>{{ $stream->name }}</option>
                                    @endforeach
                                </select>
                                @error('stream_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="subject_id">Major Subject</label>
                                <select id="subject_id" name="subject_id" class="form-control @error('subject_id') is-invalid @enderror" data-placeholder="Select major subject" data-disabled-placeholder="Select stream first" required {{ old('stream_id') ? '' : 'disabled' }}>
                                    <option value="">{{ old('stream_id') ? 'Select major subject' : 'Select stream first' }}</option>
                                    @foreach($subjects as $subject)
                                        <option value="{{ $subject->id }}" {{ (string) old('subject_id') === (string) $subject->id ? 'selected' : '' }}>{{ $subject->name }}</option>
                                    @endforeach
                                </select>
                                @error('subject_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-3 col-6">
                                <label for="class_semester">Class/Semester</label>
                                <select id="class_semester" name="class_semester" class="form-control @error('class_semester') is-invalid @enderror" required>
                                    <option value="">Select</option>
                                    @foreach($classSemesters as $value => $label)
                                        <option value="{{ $value }}" {{ old('class_semester') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('class_semester')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-group col-md-3 col-6">
                                <label for="year_of_study">Year of Study</label>
                                <select id="year_of_study" name="year_of_study" class="form-control @error('year_of_study') is-invalid @enderror" required>
                                    <option value="">Select</option>
                                    @foreach($yearsOfStudy as $value => $label)
                                        <option value="{{ $value }}" {{ old('year_of_study') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('year_of_study')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="registration_number">Registration Number</label>
                                <input type="text" id="registration_number" name="registration_number" class="form-control @error('registration_number') is-invalid @enderror" value="{{ old('registration_number') }}" maxlength="100" required>
                                @error('registration_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="roll_number">Roll Number</label>
                                <input type="text" id="roll_number" name="roll_number" class="form-control @error('roll_number') is-invalid @enderror" value="{{ old('roll_number') }}" maxlength="100" required>
                                @error('roll_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label for="academic_session">Academic Session</label>
                                <select id="academic_session" name="academic_session" class="form-control @error('academic_session') is-invalid @enderror" required>
                                    <option value="">Select Session</option>
                                    @foreach($academicSessions as $value => $label)
                                        <option value="{{ $value }}" {{ old('academic_session') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('academic_session')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="ireg-card">
                        <h6>Payment Method</h6>
                        <div class="form-row">
                            <div class="form-group col-md-12">
                                <label>Select Payment Method</label>
                                <div class="payment-method-group">
                                    <div class="form-check" style="margin-bottom: 15px;">
                                        <input type="radio" class="form-check-input" id="payment_offline" name="payment_method" value="offline" {{ old('payment_method') === 'offline' || !old('payment_method') ? 'checked' : '' }} required>
                                        <label class="form-check-label" for="payment_offline">
                                            <strong>Offline Payment</strong> - Admin will verify and approve your payment
                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input type="radio" class="form-check-input" id="payment_online" name="payment_method" value="online" {{ old('payment_method') === 'online' ? 'checked' : '' }} required disabled>
                                        <label class="form-check-label" for="payment_online" style="color: #999; cursor: not-allowed;">
                                            <strong>Online Payment (Coming Soon)</strong> - Pay securely with Razorpay
                        </label>
                                    </div>
                                </div>
                                @error('payment_method')<div class="invalid-feedback" style="display: block;">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn-register">Register &amp; Continue</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    var form = document.getElementById('internshipForm');
    var lookupUrls = {
        college_id: "{{ route('internship.lookup.colleges') }}",
        stream_id: "{{ route('internship.lookup.streams') }}",
        subject_id: "{{ route('internship.lookup.subjects') }}"
    };
    // University -> College -> Stream -> Subject: [parent, child, query parameter]
    var chain = [
        ['university_id', 'college_id', 'university_id'],
        ['college_id', 'stream_id', 'college_id'],
        ['stream_id', 'subject_id', 'stream_id']
    ];
    var requestToken = {};

    function el(id) { return document.getElementById(id); }

    function resetSelect(select) {
        select.innerHTML = '';
        select.appendChild(new Option(select.dataset.disabledPlaceholder, ''));
        select.disabled = true;
    }

    // A change in a parent clears every descendant, not just the direct child.
    function resetFrom(childId) {
        var started = false;
        chain.forEach(function (link) {
            if (link[1] === childId) { started = true; }
            if (started) {
                requestToken[link[1]] = (requestToken[link[1]] || 0) + 1;
                resetSelect(el(link[1]));
            }
        });
    }

    function load(parentId, childId, param) {
        var parent = el(parentId), child = el(childId);
        resetFrom(childId);
        if (!parent.value) { return; }

        var token = requestToken[childId];
        child.options[0].text = 'Loading…';

        fetch(lookupUrls[childId] + '?' + param + '=' + encodeURIComponent(parent.value), { headers: { 'Accept': 'application/json' } })
            .then(function (r) { if (!r.ok) { throw new Error(r.status); } return r.json(); })
            .then(function (rows) {
                if (token !== requestToken[childId]) { return; } // a newer selection superseded this one
                child.innerHTML = '';
                child.appendChild(new Option(rows.length ? child.dataset.placeholder : 'No options available', ''));
                rows.forEach(function (row) { child.appendChild(new Option(row.name, row.id)); });
                child.disabled = rows.length === 0;
            })
            .catch(function () {
                if (token !== requestToken[childId]) { return; }
                child.options[0].text = 'Could not load — retry';
                child.disabled = true;
            });
    }

    chain.forEach(function (link) {
        el(link[0]).addEventListener('change', function () { load(link[0], link[1], link[2]); });
    });

    // Filter Programme by College
    var collegeSelect = el('college_id'), programmeSelect = el('internship_id');
    function filterProgrammeByCollege() {
        var collegeId = collegeSelect.value;
        var allOptions = programmeSelect.querySelectorAll('option[data-college]');
        var anyVisible = false;

        allOptions.forEach(function(option) {
            if (collegeId === '' || option.dataset.college === collegeId) {
                option.style.display = 'block';
                anyVisible = true;
            } else {
                option.style.display = 'none';
            }
        });

        // Clear programme selection when college changes
        if (collegeId !== '') {
            programmeSelect.value = '';
            programmeSelect.disabled = !anyVisible;
        }
        showPrice();
    }

    collegeSelect.addEventListener('change', filterProgrammeByCollege);

    // Programme price hint
    var programme = el('internship_id'), hint = el('priceHint');
    function showPrice() {
        var o = programme.options[programme.selectedIndex];
        if (!o || !o.dataset.total) { hint.textContent = ''; return; }
        hint.textContent = parseFloat(o.dataset.tax) > 0
            ? 'Fee: ₹' + o.dataset.price + ' + ' + o.dataset.tax + '% tax = ₹' + o.dataset.total
            : 'Fee: ₹' + o.dataset.total;
    }
    programme.addEventListener('change', showPrice);

    // Initialize programme filter and price hint
    filterProgrammeByCollege();
    showPrice();

    // WhatsApp same as mobile
    var mobile = el('mobile'), whatsapp = el('whatsapp'), same = el('sameAsMobile');
    function syncWhatsapp() { if (same.checked) { whatsapp.value = mobile.value; } whatsapp.readOnly = same.checked; }
    same.addEventListener('change', syncWhatsapp);
    mobile.addEventListener('input', syncWhatsapp);
    [mobile, whatsapp].forEach(function (input) {
        input.addEventListener('input', function () { input.value = input.value.replace(/\D/g, ''); });
    });

    // Front-end validation (the server re-validates everything).
    form.addEventListener('submit', function (event) {
        var firstInvalid = null;
        Array.prototype.forEach.call(form.elements, function (field) {
            if (!field.name || field.type === 'hidden' || field.type === 'checkbox') { return; }
            var bad = !field.checkValidity();
            field.classList.toggle('is-invalid', bad);
            if (bad && !firstInvalid) { firstInvalid = field; }
        });
        if (firstInvalid) {
            event.preventDefault();
            firstInvalid.focus();
            firstInvalid.reportValidity();
        }
    });

    // The theme turns every <select> into a custom widget; these selects are rebuilt
    // dynamically, so keep them native.
    window.addEventListener('load', function () {
        if (window.jQuery && jQuery.fn.niceSelect) { jQuery('#internshipForm select').niceSelect('destroy'); }
    });
})();
</script>
@endsection
