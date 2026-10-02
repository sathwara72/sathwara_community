@extends(request()->routeIs('member.*') && auth()->check() ? 'layouts.member' : 'layouts.public')

@section('page_title', $event->title . (app()->getLocale() === 'gu' ? ' - નોંધણી ફોર્મ' : ' Registration'))

@section('content')
    @if(!request()->routeIs('member.*') || !auth()->check())
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3 sm:py-4">
    @endif
        @php
            $initialSiblingsArr = [];
            $existingSiblings = old('siblings_json');
            if (empty($existingSiblings) && isset($registration) && !empty($registration->form_data['siblings_json'])) {
                $existingSiblings = is_string($registration->form_data['siblings_json']) ? $registration->form_data['siblings_json'] : json_encode($registration->form_data['siblings_json']);
            }
            if (!empty($existingSiblings)) {
                if (is_array($existingSiblings)) {
                    $initialSiblingsArr = $existingSiblings;
                } else {
                    $decoded = json_decode($existingSiblings, true);
                    if (is_array($decoded)) {
                        $initialSiblingsArr = $decoded;
                    }
                }
            }
            if (empty($initialSiblingsArr) && isset($registration) && !empty($registration->form_data)) {
                $fd = $registration->form_data;
                if (!empty($fd['elder_brother']))
                    $initialSiblingsArr[] = ['relation' => 'Elder Brother', 'details' => $fd['elder_brother'], 'married' => $fd['elder_brother_married'] ?? 'No'];
                if (!empty($fd['younger_brother']))
                    $initialSiblingsArr[] = ['relation' => 'Younger Brother', 'details' => $fd['younger_brother'], 'married' => $fd['younger_brother_married'] ?? 'No'];
                if (!empty($fd['elder_sister']))
                    $initialSiblingsArr[] = ['relation' => 'Elder Sister', 'details' => $fd['elder_sister'], 'married' => $fd['elder_sister_married'] ?? 'No'];
                if (!empty($fd['younger_sister']))
                    $initialSiblingsArr[] = ['relation' => 'Younger Sister', 'details' => $fd['younger_sister'], 'married' => $fd['younger_sister_married'] ?? 'No'];
            }
        @endphp

        <script>
            function eventRegistrationData() {
                return {
                    selectedStudent: @json(old('student_name', '')),
                    educationType: @json(old('education_type', '')),
                    education: @json(old('education', '')),
                    otherCourse: '',
                    schoolStandard: '',
                    schoolStream: '',
                    coursesMap: {
                        'School': [
                            '1st Standard', '2nd Standard', '3rd Standard', '4th Standard',
                            '5th Standard', '6th Standard', '7th Standard', '8th Standard',
                            '9th Standard', '10th Standard', '11th Standard', '12th Standard',
                            'Other'
                        ],
                        'College': [
                            'BA – Bachelor of Arts',
                            'MA – Master of Arts',
                            'BCom – Bachelor of Commerce',
                            'MCom – Master of Commerce',
                            'BSc – Bachelor of Science',
                            'MSc – Master of Science',
                            'BCA – Bachelor of Computer Applications',
                            'MCA – Master of Computer Applications',
                            'BBA – Bachelor of Business Administration',
                            'MBA – Master of Business Administration',
                            'BBM – Bachelor of Business Management',
                            'BMS – Bachelor of Management Studies',
                            'BAF – Bachelor of Accounting and Finance',
                            'BBI – Bachelor of Banking and Insurance',
                            'BFM – Bachelor of Financial Markets',
                            'BCom (Honours) – Bachelor of Commerce (Honours)',
                            'BE – Bachelor of Engineering',
                            'ME – Master of Engineering',
                            'BTech – Bachelor of Technology',
                            'MTech – Master of Technology',
                            'MBBS – Bachelor of Medicine, Bachelor of Surgery',
                            'BDS – Bachelor of Dental Surgery',
                            'BAMS – Bachelor of Ayurvedic Medicine and Surgery',
                            'BHMS – Bachelor of Homeopathic Medicine and Surgery',
                            'BUMS – Bachelor of Unani Medicine and Surgery',
                            'BNYS – Bachelor of Naturopathy and Yogic Sciences',
                            'BPT – Bachelor of Physiotherapy',
                            'MPT – Master of Physiotherapy',
                            'BOT – Bachelor of Occupational Therapy',
                            'BSc Nursing – Bachelor of Science in Nursing',
                            'GNM – General Nursing and Midwifery',
                            'ANM – Auxiliary Nurse Midwifery',
                            'BPharm – Bachelor of Pharmacy',
                            'MPharm – Master of Pharmacy',
                            'Pharm.D – Doctor of Pharmacy',
                            'DPharm – Diploma in Pharmacy',
                            'BMLT – Bachelor of Medical Laboratory Technology',
                            'MMLT – Master of Medical Laboratory Technology',
                            'BSc Biotechnology – Bachelor of Science in Biotechnology',
                            'BSc Microbiology – Bachelor of Science in Microbiology',
                            'BSc Biochemistry – Bachelor of Science in Biochemistry',
                            'BSc Genetics – Bachelor of Science in Genetics',
                            'BSc Botany – Bachelor of Science in Botany',
                            'BSc Zoology – Bachelor of Science in Zoology',
                            'BSc Chemistry – Bachelor of Science in Chemistry',
                            'BSc Physics – Bachelor of Science in Physics',
                            'BSc Mathematics – Bachelor of Science in Mathematics',
                            'BSc Statistics – Bachelor of Science in Statistics',
                            'BSc Computer Science – Bachelor of Science in Computer Science',
                            'BSc Information Technology – Bachelor of Science in Information Technology',
                            'BSc Data Science – Bachelor of Science in Data Science',
                            'BSc Artificial Intelligence – Bachelor of Science in Artificial Intelligence',
                            'BSc Cyber Security – Bachelor of Science in Cyber Security',
                            'BSc Agriculture – Bachelor of Science in Agriculture',
                            'BSc Forestry – Bachelor of Science in Forestry',
                            'BSc Horticulture – Bachelor of Science in Horticulture',
                            'BSc Fisheries – Bachelor of Science in Fisheries',
                            'BVSc & AH – Bachelor of Veterinary Science and Animal Husbandry',
                            'BSW – Bachelor of Social Work',
                            'MSW – Master of Social Work',
                            'BJMC – Bachelor of Journalism and Mass Communication',
                            'MJMC – Master of Journalism and Mass Communication',
                            'BFA – Bachelor of Fine Arts',
                            'MFA – Master of Fine Arts',
                            'BPA – Bachelor of Performing Arts',
                            'MPA – Master of Performing Arts',
                            'BEd – Bachelor of Education',
                            'MEd – Master of Education',
                            'LLB – Bachelor of Laws',
                            'LLM – Master of Laws',
                            'BA LLB – Bachelor of Arts and Bachelor of Laws (Integrated)',
                            'BLib – Bachelor of Library Science',
                            'MLib – Master of Library Science',
                            'BDes – Bachelor of Design',
                            'MDes – Master of Design',
                            'BFD – Bachelor of Fashion Design',
                            'BID – Bachelor of Interior Design',
                            'BArch – Bachelor of Architecture',
                            'BHM – Bachelor of Hotel Management',
                            'BTTM – Bachelor of Tourism and Travel Management',
                            'CA – Chartered Accountancy',
                            'CS – Company Secretary',
                            'CMA – Cost and Management Accountant',
                            'CFA – Chartered Financial Analyst',
                            'Other'
                        ],
                        'Diploma': [
                            'Diploma in Computer Engineering',
                            'Diploma in Information Technology',
                            'Diploma in Civil Engineering',
                            'Diploma in Mechanical Engineering',
                            'Diploma in Electrical Engineering',
                            'Diploma in Electronics & Communication Engineering',
                            'Diploma in Electronics Engineering',
                            'Diploma in Automobile Engineering',
                            'Diploma in Chemical Engineering',
                            'Diploma in Textile Engineering',
                            'Diploma in Plastic Engineering',
                            'Diploma in Production Engineering',
                            'Diploma in Metallurgy Engineering',
                            'Diploma in Mechatronics Engineering',
                            'Diploma in Instrumentation & Control Engineering',
                            'Diploma in Power Electronics Engineering',
                            'Diploma in Mining Engineering',
                            'Diploma in Environmental Engineering',
                            'Diploma in Architecture',
                            'Diploma in Interior Design',
                            'Diploma in Fashion Design',
                            'Diploma in Graphic Design',
                            'Diploma in Printing Technology',
                            'Diploma in Ceramic Technology',
                            'Diploma in Marine Engineering',
                            'Diploma in Aeronautical Engineering',
                            'Diploma in Petroleum Engineering',
                            'Diploma in Robotics & Automation',
                            'Diploma in Artificial Intelligence',
                            'Diploma in Machine Learning',
                            'Diploma in Data Science',
                            'Diploma in Cyber Security',
                            'Diploma in Renewable Energy',
                            'Diploma in Fire & Safety Engineering',
                            'Diploma in Hotel Management',
                            'Diploma in Travel & Tourism',
                            'Diploma in Pharmacy (D.Pharm)',
                            'Diploma in Medical Laboratory Technology (DMLT)',
                            'Diploma in Radiology',
                            'Diploma in Operation Theatre Technology',
                            'Diploma in Optometry',
                            'Diploma in Nursing',
                            'Diploma in Physiotherapy',
                            'Other'
                        ],
                        'ITI': [
                            'COPA (Computer Operator & Programming Assistant)',
                            'ICTSM (Information Communication Technology System Maintenance)',
                            'Electrician',
                            'Wireman',
                            'Fitter',
                            'Turner',
                            'Machinist',
                            'Welder',
                            'Plumber',
                            'Carpenter',
                            'Mason (Building Constructor)',
                            'Painter (General)',
                            'Draughtsman Civil',
                            'Draughtsman Mechanical',
                            'Surveyor',
                            'Mechanic Motor Vehicle (MMV)',
                            'Diesel Mechanic',
                            'Tractor Mechanic',
                            'Mechanic Auto Electrical & Electronics',
                            'Mechanic Machine Tool Maintenance',
                            'Mechanic Industrial Electronics',
                            'Mechanic Computer Hardware',
                            'Electronics Mechanic',
                            'Instrument Mechanic',
                            'Refrigeration & Air Conditioning Technician',
                            'Tool & Die Maker',
                            'Foundryman',
                            'Sheet Metal Worker',
                            'Pump Operator cum Mechanic',
                            'Sewing Technology',
                            'Dress Making',
                            'Fashion Design Technology',
                            'Cutting & Sewing',
                            'Food Production',
                            'Baker & Confectioner',
                            'Front Office Assistant',
                            'Housekeeper',
                            'Health Sanitary Inspector',
                            'Dental Laboratory Technician',
                            'Fruit & Vegetable Processor',
                            'Agri Machinery Mechanic',
                            'Solar Technician',
                            'Lift & Escalator Mechanic',
                            'Stenographer (English)',
                            'Stenographer (Hindi)',
                            'Secretarial Practice',
                            'Library & Information Science Assistant',
                            'Desktop Publishing Operator (DTP)',
                            'Data Entry Operator',
                            'Cosmetology',
                            'Spa Therapy',
                            'Hair & Skin Care',
                            'Other'
                        ],
                        'Other': ['Other']
                    },
                    onStandardChange() {
                        this.schoolStream = '';
                        this.otherCourse = '';
                        this.updateEducationFromSchool();
                    },
                    onStreamChange() {
                        this.otherCourse = '';
                        this.updateEducationFromSchool();
                    },
                    updateEducationFromSchool() {
                        if (this.educationType !== 'School') return;
                        if (this.schoolStandard === '11th Standard' || this.schoolStandard === '12th Standard') {
                            if (this.schoolStream === 'Other') {
                                this.education = 'Other';
                            } else if (this.schoolStream) {
                                this.education = this.schoolStandard + ' (' + this.schoolStream + ')';
                            } else {
                                this.education = '';
                            }
                        } else if (this.schoolStandard === 'Other') {
                            this.education = 'Other';
                        } else {
                            this.education = this.schoolStandard;
                        }
                    },
                    parseSchoolEducation(rawEdu) {
                        if (!rawEdu) return;
                        let stdMatches = rawEdu.match(/(1st|2nd|3rd|4th|5th|6th|7th|8th|9th|10th|11th|12th)\s*(Standard|Std)?/i);
                        if (stdMatches) {
                            let num = stdMatches[1].toLowerCase();
                            this.schoolStandard = num + ' Standard';
                            if (num === '11th' || num === '12th') {
                                if (/science/i.test(rawEdu)) this.schoolStream = 'Science';
                                else if (/commerce/i.test(rawEdu)) this.schoolStream = 'Commerce';
                                else if (/arts/i.test(rawEdu)) this.schoolStream = 'Arts';
                                else {
                                    this.schoolStream = 'Other';
                                    this.otherCourse = rawEdu;
                                }
                            }
                        } else {
                            this.schoolStandard = 'Other';
                            this.otherCourse = rawEdu;
                        }
                        this.updateEducationFromSchool();
                    },
                    get coursesList() {
                        if (!this.educationType) return [];
                        let et = this.educationType;
                        if (et === 'Primary' || et === 'Secondary' || et === 'Higher Secondary') et = 'School';
                        if (et === 'College (UG / PG / Professional)') et = 'College';
                        if (et === 'Diploma / Polytechnic') et = 'Diploma';
                        if (et === 'ITI Trades') et = 'ITI';
                        return this.coursesMap[et] || this.coursesMap['Other'] || [];
                    },
                    courseDropdownOpen: false,
                    courseSearch: '',
                    get filteredCoursesList() {
                        const list = this.coursesList;
                        if (!this.courseSearch.trim()) return list;
                        const q = this.courseSearch.toLowerCase();
                        return list.filter(c => c.toLowerCase().includes(q));
                    },
                    schoolCollege: @json(old('school_college', '')),
                    totalMarks: @json(old('total_marks', '')),
                    receivedMarks: @json(old('received_marks', '')),
                    percentage: @json(old('percentage', '')),
                    remarks: @json(old('remarks', '')),
                    isEditing: false,
                    editingRegistrationId: null,
                    previewLang: @json(app()->getLocale() === 'gu' ? 'gu' : 'en'),
                    marksheetUrl: '',
                    mainPageTab: @json(
                        request('tab') ? request('tab') :
                        ((session('success') && isset($registrations) && $registrations->count() > 0) ? 'submitted' :
                        ((isset($registrations) && $registrations->count() > 0
                            && (!($event->has_registration_form ?? $event->registration_option)
                                || (!empty($event->form_end_date)
                                    && now()->toDateString() > \Carbon\Carbon::parse($event->form_end_date)->toDateString()))) ? 'submitted' : 'form'))
                    ),
                    yuvaTab: 1,
                    showDetailsModal: false,
                    showSiblingModal: false,
                    siblings: @json($initialSiblingsArr),
                    legacyElderB: '',
                    legacyElderBM: '',
                    legacyYoungerB: '',
                    legacyYoungerBM: '',
                    legacyElderS: '',
                    legacyElderSM: '',
                    legacyYoungerS: '',
                    legacyYoungerSM: '',
                    newSibling: {
                        relation: 'Elder Brother',
                        details: '',
                        married: 'No',
                        occupation: ''
                    },
                    init() {
                        this.syncSiblingFields();
                        this.calcPercentage();
                        let rawEt = this.educationType;
                        if (rawEt === 'Primary' || rawEt === 'Secondary' || rawEt === 'Higher Secondary') this.educationType = 'School';
                        if (rawEt === 'College (UG / PG / Professional)') this.educationType = 'College';
                        if (rawEt === 'Diploma / Polytechnic') this.educationType = 'Diploma';
                        if (rawEt === 'ITI Trades') this.educationType = 'ITI';

                        if (this.educationType === 'School' && this.education) {
                            this.parseSchoolEducation(this.education);
                        } else if (this.education && this.educationType) {
                            let availableCourses = this.coursesList;
                            if (this.educationType === 'Other') {
                                this.otherCourse = this.education;
                                this.education = 'Other';
                            } else if (!availableCourses.includes(this.education) || this.education === 'Other') {
                                this.otherCourse = this.education === 'Other' ? '' : this.education;
                                this.education = 'Other';
                            }
                        }
                    },
                    editRegistration(reg) {
                        this.showDetailsModal = false;
                        this.mainPageTab = 'form';
                        let fd = reg.form_data || {};
                        this.isEditing = true;
                        this.editingRegistrationId = reg.id;
                        this.selectedStudent = fd.student_name || fd.full_name || ((fd.first_name || '') + ' ' + (fd.surname || '')).trim() || 'Candidate';

                        // 1. Inam Vitaran bindings
                        let rawEt = fd.education_type || '';
                        let mappedEt = rawEt;
                        if (rawEt === 'Primary' || rawEt === 'Secondary' || rawEt === 'Higher Secondary') mappedEt = 'School';
                        if (rawEt === 'College (UG / PG / Professional)') mappedEt = 'College';
                        if (rawEt === 'Diploma / Polytechnic') mappedEt = 'Diploma';
                        if (rawEt === 'ITI Trades') mappedEt = 'ITI';
                        this.educationType = mappedEt || 'School';

                        let rawEdu = fd.education || '';
                        this.schoolStandard = '';
                        this.schoolStream = '';
                        this.otherCourse = '';

                        if (this.educationType === 'School') {
                            this.parseSchoolEducation(rawEdu);
                        } else if (this.educationType === 'Other') {
                            this.education = 'Other';
                            this.otherCourse = rawEdu;
                        } else {
                            let availableCourses = this.coursesList;
                            if (availableCourses.includes(rawEdu) && rawEdu !== 'Other') {
                                this.education = rawEdu;
                                this.otherCourse = '';
                            } else if (rawEdu) {
                                this.education = 'Other';
                                this.otherCourse = rawEdu;
                            } else {
                                this.education = rawEdu;
                                this.otherCourse = '';
                            }
                        }

                        this.schoolCollege = fd.school_college || '';
                        this.totalMarks = fd.total_marks || '';
                        this.receivedMarks = fd.received_marks || '';
                        this.percentage = fd.percentage || '';
                        this.remarks = fd.remarks || '';
                        this.marksheetUrl = fd.marksheet_url || '';
                        this.calcPercentage();

                        // 2. Yuva Melo Form Fields Prefill
                        this.$nextTick(() => {
                            const form = document.getElementById('eventDynamicRegisterForm');
                            if (form) {
                                for (const [key, val] of Object.entries(fd)) {
                                    const inputs = form.querySelectorAll(`[name="${key}"]`);
                                    inputs.forEach(input => {
                                        if (input.type === 'file') return;
                                        if (input.type === 'radio') {
                                            input.checked = (input.value == val);
                                        } else if (input.type === 'checkbox') {
                                            input.checked = Boolean(val);
                                        } else {
                                            input.value = val;
                                        }
                                    });
                                }
                            }
                        });

                        // 3. Siblings list prefill
                        this.siblings = [];
                        if (fd.siblings_json) {
                            try {
                                this.siblings = typeof fd.siblings_json === 'string' ? JSON.parse(fd.siblings_json) : fd.siblings_json;
                            } catch (e) {
                                this.siblings = [];
                            }
                        }
                        this.syncSiblingFields();

                        // Switch to step 1
                        this.yuvaTab = 1;

                        // Scroll smoothly to form card
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    },
                    cancelEdit() {
                        this.isEditing = false;
                        this.editingRegistrationId = null;
                        this.selectedStudent = '';
                        this.educationType = '';
                        this.education = '';
                        this.otherCourse = '';
                        this.schoolStandard = '';
                        this.schoolStream = '';
                        this.schoolCollege = '';
                        this.totalMarks = '';
                        this.receivedMarks = '';
                        this.percentage = '';
                        this.remarks = '';
                        this.marksheetUrl = '';
                        this.siblings = [];
                        this.syncSiblingFields();
                        const form = document.getElementById('eventDynamicRegisterForm');
                        if (form) form.reset();
                    },
                    addSibling() {
                        if (!this.newSibling.relation) return;
                        this.siblings.push({ ...this.newSibling });
                        this.newSibling = { relation: 'Elder Brother', details: '', married: 'No', occupation: '' };
                        this.showSiblingModal = false;
                        this.syncSiblingFields();
                    },
                    removeSibling(index) {
                        this.siblings.splice(index, 1);
                        this.syncSiblingFields();
                    },
                    syncSiblingFields() {
                        this.legacyElderB = this.siblings.filter(s => s.relation === 'Elder Brother').map(s => s.details || '1').join(', ');
                        this.legacyElderBM = this.siblings.some(s => s.relation === 'Elder Brother' && s.married === 'Yes') ? 'Yes' : (this.siblings.some(s => s.relation === 'Elder Brother') ? 'No' : '');

                        this.legacyYoungerB = this.siblings.filter(s => s.relation === 'Younger Brother').map(s => s.details || '1').join(', ');
                        this.legacyYoungerBM = this.siblings.some(s => s.relation === 'Younger Brother' && s.married === 'Yes') ? 'Yes' : (this.siblings.some(s => s.relation === 'Younger Brother') ? 'No' : '');

                        this.legacyElderS = this.siblings.filter(s => s.relation === 'Elder Sister').map(s => s.details || '1').join(', ');
                        this.legacyElderSM = this.siblings.some(s => s.relation === 'Elder Sister' && s.married === 'Yes') ? 'Yes' : (this.siblings.some(s => s.relation === 'Elder Sister') ? 'No' : '');

                        this.legacyYoungerS = this.siblings.filter(s => s.relation === 'Younger Sister').map(s => s.details || '1').join(', ');
                        this.legacyYoungerSM = this.siblings.some(s => s.relation === 'Younger Sister' && s.married === 'Yes') ? 'Yes' : (this.siblings.some(s => s.relation === 'Younger Sister') ? 'No' : '');
                    },
                    selectedRegistration: {},
                    getPhotoUrl(fd) {
                        if (!fd) return '';
                        if (fd.member_photo_url) return fd.member_photo_url;
                        if (fd.selfie_url) return fd.selfie_url;
                        if (fd.whatsapp_image_url) return fd.whatsapp_image_url;
                        if (fd.member_photo && typeof fd.member_photo === 'string' && (fd.member_photo.startsWith('http') || fd.member_photo.startsWith('/storage/'))) return fd.member_photo;
                        return '';
                    },
                    getSiblingStat(fd, type, isMarried) {
                        const noneText = this.previewLang === 'en' ? 'None' : 'નથી';
                        if (!fd) return noneText;
                        let arr = [];
                        if (fd.siblings_json) {
                            try {
                                arr = typeof fd.siblings_json === 'string' ? JSON.parse(fd.siblings_json) : fd.siblings_json;
                            } catch (e) {
                                arr = [];
                            }
                        }
                        if (Array.isArray(arr) && arr.length > 0) {
                            const filtered = arr.filter(s => {
                                const relMatch = s.relation && s.relation.toLowerCase().includes(type.toLowerCase());
                                const marMatch = isMarried ? (s.married === 'Yes' || s.married === 'Married') : (s.married !== 'Yes' && s.married !== 'Married');
                                return relMatch && marMatch;
                            });
                            if (filtered.length > 0) {
                                return filtered.length + ' (' + filtered.map(s => s.details || '1').join(', ') + ')';
                            }
                        }
                        if (type.toLowerCase().includes('elder') && type.toLowerCase().includes('brother')) {
                            const val = isMarried ? fd.elder_brother_married : fd.elder_brother;
                            return (val && val !== 'No' && val !== '0') ? val : noneText;
                        }
                        if (type.toLowerCase().includes('younger') && type.toLowerCase().includes('brother')) {
                            const val = isMarried ? fd.younger_brother_married : fd.younger_brother;
                            return (val && val !== 'No' && val !== '0') ? val : noneText;
                        }
                        if (type.toLowerCase().includes('elder') && type.toLowerCase().includes('sister')) {
                            const val = isMarried ? fd.elder_sister_married : fd.elder_sister;
                            return (val && val !== 'No' && val !== '0') ? val : noneText;
                        }
                        if (type.toLowerCase().includes('younger') && type.toLowerCase().includes('sister')) {
                            const val = isMarried ? fd.younger_sister_married : fd.younger_sister;
                            return (val && val !== 'No' && val !== '0') ? val : noneText;
                        }
                        return noneText;
                    },
                    calcPercentage() {
                        let t = parseFloat(this.totalMarks);
                        let r = parseFloat(this.receivedMarks);
                        if (!isNaN(t) && !isNaN(r) && t > 0) {
                            let pct = (r / t) * 100;
                            this.percentage = pct.toFixed(2) + '%';
                        } else {
                            this.percentage = '';
                        }
                    },
                    printBiodata() {
                        const el = document.getElementById('printableBiodata');
                        if (!el) return;
                        const w = window.open('', '_blank', 'width=850,height=950');
                        if (!w) {
                            alert('Please allow pop-ups for this website to print/download biodata.');
                            return;
                        }
                        const htmlContent = el.innerHTML;
                        const docContent = '<!DOCTYPE html>' +
'<html>' +
'<head>' +
'    <meta charset="utf-8">' +
'    <title>Candidate Biodata - Satwara Yuva Melo</title>' +
'    <script src="https://cdn.tailwindcss.com"><\/script>' +
'    <style>' +
'        * { box-sizing: border-box; margin: 0; padding: 0; }' +
'        body {' +
'            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;' +
'            background: #f1f5f9;' +
'            color: #0f172a;' +
'            padding: 16px;' +
'            display: flex;' +
'            justify-content: center;' +
'        }' +
'        .biodata-sheet {' +
'            background: #ffffff;' +
'            width: 100%;' +
'            max-width: 680px;' +
'            padding: 14px;' +
'            border: 2px solid #0f172a;' +
'            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);' +
'        }' +
'        table { width: 100%; border-collapse: collapse; table-layout: fixed; }' +
'        td, th { padding: 3px 6px; word-break: break-word; font-size: 11px; }' +
'        @media print {' +
'            body {' +
'                background: #ffffff !important;' +
'                padding: 0 !important;' +
'                margin: 0 !important;' +
'                display: block !important;' +
'            }' +
'            @page {' +
'                margin: 6mm 8mm;' +
'                size: A4 portrait;' +
'            }' +
'            .biodata-sheet {' +
'                border: 2px solid #0f172a !important;' +
'                box-shadow: none !important;' +
'                max-width: 100% !important;' +
'                padding: 10px !important;' +
'                page-break-inside: avoid;' +
'            }' +
'            .no-print { display: none !important; }' +
'        }' +
'    </style>' +
'</head>' +
'<body>' +
'    <div class="biodata-sheet">' +
        htmlContent +
'    </div>' +
'</body>' +
'</html>';
                        w.document.write(docContent);
                        w.document.close();
                        setTimeout(() => { 
                            w.focus(); 
                            w.print(); 
                        }, 500);
                    }
                };
            }
        </script>

        <script type="module" src="https://unpkg.com/@dotlottie/player-component@latest/dist/dotlottie-player.mjs"></script>
        <div class="space-y-4" x-data="eventRegistrationData()">
            <!-- Top Navigation & Action Header -->
            <div class="flex items-center justify-between gap-3 flex-wrap">
                <a href="{{ auth()->check() && request()->routeIs('member.*') ? route('member.events.index') : route('home') }}"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-slate-200/80 hover:border-slate-300 rounded-xl text-xs font-bold text-slate-600 hover:text-slate-900 transition-all shadow-2xs group">
                    <svg class="w-3.5 h-3.5 text-slate-400 group-hover:-translate-x-0.5 transition-transform" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    <span>{{ __('messages.back_to_events') }}</span>
                </a>

                @if(isset($registrations) && $registrations->count() > 0)
                    <!-- Top Navigation Tabs -->
                    <div class="flex items-center gap-1.5 bg-slate-100/90 p-1 rounded-xl border border-slate-200/80">
                        <button type="button" @click="mainPageTab = 'form'"
                            :class="mainPageTab === 'form' ? 'bg-white text-slate-900 shadow-xs font-black' : 'text-slate-600 hover:text-slate-900 font-bold'"
                            class="px-3.5 py-1.5 rounded-lg text-xs transition-all flex items-center gap-1.5 cursor-pointer">
                            <span>📝 {{ __('messages.new_registration') }}</span>
                        </button>

                        <button type="button" @click="mainPageTab = 'submitted'"
                            :class="mainPageTab === 'submitted' ? 'bg-primary-600 text-white shadow-xs font-black' : 'text-slate-600 hover:text-slate-900 font-bold'"
                            class="px-3.5 py-1.5 rounded-lg text-xs transition-all flex items-center gap-1.5 cursor-pointer">
                            <span>📋 {{ __('messages.submitted_registrations') }}</span>
                            <span class="text-[10px] px-1.5 py-0.2 rounded-full font-black"
                                  :class="mainPageTab === 'submitted' ? 'bg-white text-primary-700' : 'bg-primary-500 text-white'">
                                {{ $registrations->count() }}
                            </span>
                        </button>
                    </div>
                @endif
            </div>

            <!-- ================= TAB 1: NEW REGISTRATION FORM ================= -->
            <div x-show="mainPageTab === 'form'" class="space-y-3">
            @php
                $isRegistrationFormDisabled = !($event->has_registration_form ?? $event->registration_option);
                $today = now()->toDateString();
                $formNotYetOpen = !$isRegistrationFormDisabled && !empty($event->form_start_date) && $today < \Carbon\Carbon::parse($event->form_start_date)->toDateString();
                $formPastDeadline = !$isRegistrationFormDisabled && !empty($event->form_end_date) && $today > \Carbon\Carbon::parse($event->form_end_date)->toDateString();
                $isRegistrationClosed = $isRegistrationFormDisabled || $formNotYetOpen || $formPastDeadline;
            @endphp

            <!-- DEFAULT / CLOSED NOTICE BANNER -->
            @if($isRegistrationFormDisabled)
                <div
                    class="p-4 bg-rose-50 border border-rose-200 rounded-2xl text-xs text-rose-800 flex items-center gap-2.5 font-bold shadow-2xs">
                    <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span>Notice: Registration form is disabled for this event.@if(!empty($event->form_end_date)) Form fill-up closed on {{ date('d-M-Y', strtotime($event->form_end_date)) }}.@endif</span>
                </div>
            @elseif(!$isRegistrationClosed)
                <div
                    class="p-3.5 sm:p-4 bg-amber-50/90 border border-amber-200/90 rounded-2xl text-xs text-amber-900 flex items-start gap-3 shadow-2xs">
                    <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div class="space-y-1 flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-2 flex-wrap">
                            <span class="text-xs font-black text-amber-900 uppercase tracking-wide">
                                {{ __('messages.notice_instructions') }}
                            </span>
                            @if(!empty($event->form_end_date))
                                <span
                                    class="inline-flex items-center gap-1 text-[9px] font-bold text-amber-700 bg-amber-100/90 px-2 py-0.5 rounded-full border border-amber-200/80 shrink-0">
                                    <svg class="w-3 h-3 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <span>{{ __('messages.last_date') }}: {{ date('d-M-Y', strtotime($event->form_end_date)) }}</span>
                                </span>
                            @endif
                        </div>
                        <div class="text-[11px] font-medium text-amber-800 leading-relaxed">
                            @if(in_array($event->event_type ?? 'normal', ['inam_vitaran', 'yuva_melo']))
                                {{ __('messages.notice_instructions_desc') }}
                            @elseif(!empty($event->description))
                                {!! $event->description !!}
                            @else
                                {{ __('messages.notice_instructions_desc') }}
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            @if($isRegistrationClosed)
                <!-- REGISTRATION CLOSED CARD WITH DYNAMIC ANIMATIONS -->
                <div
                    class="bg-white rounded-2xl border border-slate-200/80 p-6 sm:p-8 text-center shadow-2xs space-y-4 flex flex-col items-center justify-center relative overflow-hidden transition-all duration-300 hover:shadow-md">
                    <!-- Ambient background floating glow animation -->
                    <div
                        class="absolute -top-20 -right-20 w-56 h-56 bg-rose-100/50 rounded-full blur-3xl animate-float pointer-events-none">
                    </div>
                    <div
                        class="absolute -bottom-20 -left-20 w-56 h-56 bg-amber-100/50 rounded-full blur-3xl animate-float-reverse pointer-events-none">
                    </div>

                    <!-- Vector Graphic Animated Lock Badge -->
                    <div class="relative flex items-center justify-center z-10 py-1">
                        <div
                            class="w-16 h-16 sm:w-20 sm:h-20 rounded-3xl bg-rose-50 border border-rose-200/80 flex items-center justify-center text-rose-600 shadow-2xs relative group animate-float">
                            <!-- Outer pulsing rings -->
                            <div class="absolute -inset-1.5 rounded-3xl bg-rose-500/15 animate-ping opacity-75"></div>
                            <div class="absolute -inset-3 rounded-3xl bg-rose-400/10 animate-pulse-glow"></div>

                            <svg class="w-8 h-8 sm:w-10 sm:h-10 text-rose-500 relative z-10 transform group-hover:scale-110 group-hover:rotate-6 transition-transform duration-300"
                                fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                    </div>

                    <!-- Details with Animations -->
                    <div class="max-w-md space-y-1.5 z-10">
                        <div
                            class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-rose-100/90 border border-rose-200/90 text-rose-800 font-extrabold text-[10px] uppercase tracking-wider shadow-2xs hover:scale-105 transition-transform">
                            <span class="relative flex h-2 w-2">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-600 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-rose-600"></span>
                            </span>
                            <span>{{ $isRegistrationFormDisabled ? 'Form Disabled' : ($formNotYetOpen ? 'Not Open Yet' : 'Registration Closed') }}</span>
                        </div>

                        <h3 class="text-base sm:text-lg font-black text-slate-900 tracking-tight">
                            {{ $isRegistrationFormDisabled ? 'Registration Form Is Disabled' : ($formNotYetOpen ? 'Form Fill-up Has Not Started' : 'Form Fill-up Is Closed') }}</h3>

                        <p class="text-xs text-slate-500 font-medium leading-relaxed">
                            @if($isRegistrationFormDisabled)
                                Online registration form is not enabled for <strong
                                    class="text-slate-800 font-bold">{{ $event->title }}</strong>.
                                @if(!empty($event->form_end_date))
                                    Form fill-up closed on
                                    <span
                                        class="font-bold text-rose-700 bg-rose-50 px-2 py-0.5 rounded border border-rose-200/80 inline-block">{{ date('d-M-Y', strtotime($event->form_end_date)) }}</span>.
                                @endif
                            @elseif($formNotYetOpen)
                                Form fill-up for <strong class="text-slate-800 font-bold">{{ $event->title }}</strong> opens on
                                <span
                                    class="font-bold text-rose-700 bg-rose-50 px-2 py-0.5 rounded border border-rose-200/80 inline-block hover:scale-105 transition-transform animate-pulse">{{ date('d-M-Y', strtotime($event->form_start_date)) }}</span>.
                                Please check back then.
                            @else
                                The deadline for submitting registrations for <strong
                                    class="text-slate-800 font-bold">{{ $event->title }}</strong> passed on
                                <span
                                    class="font-bold text-rose-700 bg-rose-50 px-2 py-0.5 rounded border border-rose-200/80 inline-block hover:scale-105 transition-transform animate-pulse">{{ date('d-M-Y', strtotime($event->form_end_date)) }}</span>.
                                Form fill-up is disabled for this event.
                            @endif
                        </p>
                    </div>


                </div>
            @else
                <!-- MAIN FORM CARD CONTAINER -->
                <div id="registrationFormCard"
                    class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden w-full transition-all">
                    <div class="p-4 sm:p-6 space-y-4">

                        <!-- Edit Mode Banner -->
                        <div x-show="isEditing"
                            class="bg-amber-50 border border-amber-200 p-3 rounded-xl flex items-center justify-between gap-3 text-xs"
                            x-cloak>
                            <div class="flex items-center gap-2">
                                <span class="text-amber-600 font-extrabold">✏️ Editing Registration for:</span>
                                <span class="font-black text-amber-900" x-text="selectedStudent"></span>
                            </div>
                            <button type="button" @click="cancelEdit()"
                                class="px-3 py-1 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs rounded-lg border border-slate-200 shadow-2xs">
                                ✕ Cancel Edit
                            </button>
                        </div>

                        <!-- Registration Form -->
                        <form method="POST" action="{{ route('events.public_register', $event->id) }}"
                            id="eventDynamicRegisterForm"
                            enctype="multipart/form-data" class="space-y-4" novalidate>
                            @csrf
                            <input type="hidden" name="registration_id" id="editing_registration_id" :value="editingRegistrationId">
                            <input type="hidden" name="razorpay_payment_id" id="dynamic_razorpay_payment_id">
                            <input type="hidden" name="redirect_to" value="{{ request()->fullUrl() }}">

                            <!-- Inline Form Error Notification Banner -->
                            <div id="yuvaFormErrorBanner" style="display: none;"
                                class="bg-rose-50 border border-rose-200 text-rose-800 p-3.5 rounded-xl text-xs font-bold flex items-center justify-between gap-3 shadow-xs">
                                <div class="flex items-center gap-2">
                                    <span class="text-rose-600 text-base">⚠️</span>
                                    <span id="yuvaFormErrorMessage"></span>
                                </div>
                                <button type="button" onclick="document.getElementById('yuvaFormErrorBanner').style.display='none'"
                                    class="text-rose-500 hover:text-rose-700 font-black cursor-pointer">✕</button>
                            </div>

                            @if(($event->event_type ?? 'normal') === 'inam_vitaran')
                                <!-- Inam Vitaran Academic & Marksheet Form Fields -->
                                <div class="space-y-4 text-xs">
                                    @php
                                        $familyMembers = $familyMembers ?? (auth()->check() ? auth()->user()->familyMembers : collect());
                                    @endphp
                                    <div class="space-y-1">
                                        <label class="text-[11px] font-bold text-slate-700 flex items-center justify-between">
                                            <span>{{ __('messages.student_candidate_full_name') }} <span class="text-rose-500">*</span></span>
                                            <span class="text-[10px] font-normal text-slate-400">{{ __('messages.select_student_from_family') }}</span>
                                        </label>
                                        @if($familyMembers->count() > 0)
                                            <select name="student_name" x-model="selectedStudent" required
                                                class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:bg-white focus:border-primary-500 transition-all outline-none">
                                                <option value="">-- {{ __('messages.select_student_child') }} --</option>
                                                @foreach($familyMembers as $fm)
                                                    <option value="{{ $fm->name }}">{{ $fm->name }} ({{ $fm->relationship }})</option>
                                                @endforeach
                                                <template x-if="selectedStudent && !@json($familyMembers->pluck('name')).includes(selectedStudent)">
                                                    <option :value="selectedStudent" x-text="selectedStudent" selected></option>
                                                </template>
                                            </select>
                                        @else
                                            <div
                                                class="p-3.5 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-800 space-y-2">
                                                <p class="font-bold">{{ __('messages.no_family_members_found') }}</p>
                                                <p class="text-[11px]">{{ __('messages.add_children_family_first') }}</p>
                                                <a href="{{ route('member.family.index') }}"
                                                    class="inline-block px-3.5 py-1.5 bg-amber-600 text-white font-bold rounded-lg text-[11px]">
                                                    + {{ __('messages.add_family_member') }}
                                                </a>
                                            </div>
                                        @endif
                                    </div>

                                    <template x-if="selectedStudent !== ''">
                                        <div class="space-y-4 pt-1">
                                            <!-- STEP 1: Education Type Dropdown -->
                                            <div class="space-y-1">
                                                <label class="text-[11px] font-bold text-slate-700 flex items-center gap-1">
                                                    {{ __('messages.education_type') }} <span class="text-rose-500">*</span>
                                                </label>
                                                <select name="education_type" x-model="educationType" required
                                                    @change="education = ''; otherCourse = ''; schoolStandard = ''; schoolStream = ''"
                                                    class="w-full px-3.5 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:bg-white focus:border-primary-500 focus:ring-2 focus:ring-primary-100 transition-all outline-none">
                                                    <option value="">-- Select Education Type --</option>
                                                    <option value="School">School (1st to 12th Standard)</option>
                                                    <option value="College">College (UG / PG / Professional)</option>
                                                    <option value="Diploma">Diploma / Polytechnic</option>
                                                    <option value="ITI">ITI Trades</option>
                                                    <option value="Other">Other</option>
                                                </select>
                                            </div>

                                            <!-- Hidden input for form submission -->
                                            <input type="hidden" name="education"
                                                :value="(educationType === 'Other' || education === 'Other' || (educationType === 'School' && (schoolStandard === 'Other' || schoolStream === 'Other'))) ? otherCourse : education">

                                            <!-- STEP 2A: School Standard Selection (when Education Type is 'School') -->
                                            <div class="space-y-3" x-show="educationType === 'School'">
                                                <!-- Standard Dropdown -->
                                                <div class="space-y-1">
                                                    <label class="text-[11px] font-bold text-slate-700 flex items-center gap-1">
                                                        {{ __('messages.standard_class') }} <span class="text-rose-500">*</span>
                                                    </label>
                                                    <select x-model="schoolStandard" @change="onStandardChange()"
                                                        :required="educationType === 'School'"
                                                        class="w-full px-3.5 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:bg-white focus:border-primary-500 focus:ring-2 focus:ring-primary-100 transition-all outline-none">
                                                        <option value="">-- {{ __('messages.select_standard') }} --</option>
                                                        <option value="1st Standard">1st Standard</option>
                                                        <option value="2nd Standard">2nd Standard</option>
                                                        <option value="3rd Standard">3rd Standard</option>
                                                        <option value="4th Standard">4th Standard</option>
                                                        <option value="5th Standard">5th Standard</option>
                                                        <option value="6th Standard">6th Standard</option>
                                                        <option value="7th Standard">7th Standard</option>
                                                        <option value="8th Standard">8th Standard</option>
                                                        <option value="9th Standard">9th Standard</option>
                                                        <option value="10th Standard">10th Standard</option>
                                                        <option value="11th Standard">11th Standard</option>
                                                        <option value="12th Standard">12th Standard</option>
                                                        <option value="Other">{{ __('messages.other') }}</option>
                                                    </select>
                                                </div>

                                                <!-- Stream Dropdown (for 11th & 12th) -->
                                                <div class="space-y-1"
                                                    x-show="schoolStandard === '11th Standard' || schoolStandard === '12th Standard'"
                                                    x-transition:enter="transition ease-out duration-200"
                                                    x-transition:enter-start="opacity-0 -translate-y-1"
                                                    x-transition:enter-end="opacity-100 translate-y-0">
                                                    <label class="text-[11px] font-bold text-slate-700 flex items-center gap-1">
                                                        {{ __('messages.stream_branch') }} <span class="text-rose-500">*</span>
                                                    </label>
                                                    <select x-model="schoolStream" @change="onStreamChange()"
                                                        :required="educationType === 'School' && (schoolStandard === '11th Standard' || schoolStandard === '12th Standard')"
                                                        class="w-full px-3.5 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:bg-white focus:border-primary-500 focus:ring-2 focus:ring-primary-100 transition-all outline-none">
                                                        <option value="">-- {{ __('messages.select_stream') }} --</option>
                                                        <option value="Science">Science</option>
                                                        <option value="Commerce">Commerce</option>
                                                        <option value="Arts">Arts</option>
                                                        <option value="Other">{{ __('messages.other') }}</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <!-- STEP 2B: Searchable Course Dropdown (when Education Type is College, Diploma, or ITI) -->
                                            <div class="space-y-1"
                                                x-show="educationType === 'College' || educationType === 'Diploma' || educationType === 'ITI'"
                                                x-transition:enter="transition ease-out duration-200"
                                                x-transition:enter-start="opacity-0 -translate-y-1"
                                                x-transition:enter-end="opacity-100 translate-y-0" style="display:none">
                                                <label class="text-[11px] font-bold text-slate-700 flex items-center gap-1">
                                                    {{ __('messages.course_degree') }} <span class="text-rose-500">*</span>
                                                </label>

                                                <!-- Custom Dropdown Trigger -->
                                                <div class="relative" @click.away="courseDropdownOpen = false">
                                                    <button type="button"
                                                        @click="courseDropdownOpen = !courseDropdownOpen; if(courseDropdownOpen) $nextTick(() => $refs.courseSearchInput.focus())"
                                                        class="w-full px-3.5 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-left flex items-center justify-between gap-2 focus:bg-white focus:border-primary-500 focus:ring-2 focus:ring-primary-100 transition-all outline-none"
                                                        :class="courseDropdownOpen ? 'border-primary-500 ring-2 ring-primary-100 bg-white' : ''">
                                                        <span class="text-slate-900 font-bold"
                                                            x-text="education || '- {{ __('messages.select_course') }} -'"></span>
                                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                            class="w-3.5 h-3.5 text-slate-400 shrink-0 transition-transform duration-200"
                                                            :class="courseDropdownOpen ? 'rotate-180' : ''" fill="none"
                                                            viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                d="M19 9l-7 7-7-7" />
                                                        </svg>
                                                    </button>

                                                    <!-- Dropdown Panel -->
                                                    <div x-show="courseDropdownOpen"
                                                        x-transition:enter="transition ease-out duration-150"
                                                        x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                                                        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                                        x-transition:leave="transition ease-in duration-100"
                                                        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                                                        x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                                                        style="display:none"
                                                        class="absolute z-[999] left-0 right-0 top-full mt-1 bg-white border border-slate-200 rounded-xl shadow-xl overflow-hidden">

                                                        <!-- Search Box -->
                                                        <div class="p-2 border-b border-slate-100 bg-slate-50">
                                                            <div
                                                                class="flex items-center gap-2 px-2.5 py-1.5 bg-white border border-slate-200 rounded-lg">
                                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                                    class="w-3 h-3 text-slate-400 shrink-0" fill="none"
                                                                    viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                        stroke-width="2"
                                                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0" />
                                                                </svg>
                                                                <input type="text" x-ref="courseSearchInput" x-model="courseSearch"
                                                                    placeholder="{{ __('messages.search_course') }}"
                                                                    class="flex-1 text-xs font-medium text-slate-700 bg-transparent outline-none placeholder-slate-400">
                                                                <button type="button" x-show="courseSearch"
                                                                    @click="courseSearch = ''"
                                                                    class="text-slate-300 hover:text-slate-500">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3"
                                                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                                            stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                                    </svg>
                                                                </button>
                                                            </div>
                                                        </div>

                                                        <!-- Course List -->
                                                        <div class="overflow-y-auto" style="max-height: 200px;">
                                                            <template x-if="filteredCoursesList.length === 0">
                                                                <div class="px-4 py-3 text-xs text-slate-400 text-center">{{ __('messages.no_course_found') }}</div>
                                                            </template>
                                                            <template x-for="course in filteredCoursesList" :key="course">
                                                                <div @click="education = course; courseDropdownOpen = false; courseSearch = ''"
                                                                    class="px-3.5 py-2 text-xs font-medium cursor-pointer transition-colors"
                                                                    :class="education === course ? 'bg-primary-50 text-primary-600 font-bold' : 'text-slate-700 hover:bg-slate-50'"
                                                                    x-text="course">
                                                                </div>
                                                            </template>
                                                        </div>

                                                        <!-- Footer count -->
                                                        <div
                                                            class="px-3 py-1.5 border-t border-slate-100 bg-slate-50 text-[10px] text-slate-400 font-medium">
                                                            <span x-text="filteredCoursesList.length + ' course(s)'"></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Custom Course / Stream Input (when 'Other' is selected anywhere) -->
                                            <div class="space-y-1"
                                                x-show="educationType === 'Other' || (educationType !== 'School' && educationType !== '' && education === 'Other') || (educationType === 'School' && (schoolStandard === 'Other' || schoolStream === 'Other'))"
                                                x-transition:enter="transition ease-out duration-200"
                                                x-transition:enter-start="opacity-0 -translate-y-1"
                                                x-transition:enter-end="opacity-100 translate-y-0" style="display:none">
                                                <label class="text-[11px] font-bold text-slate-700 flex items-center gap-1">
                                                    {{ __('messages.enter_course_standard_name') }} <span class="text-rose-500">*</span>
                                                </label>
                                                <input type="text" x-model="otherCourse"
                                                    :required="educationType === 'Other' || (educationType !== 'School' && education === 'Other') || (educationType === 'School' && (schoolStandard === 'Other' || schoolStream === 'Other'))"
                                                    placeholder="{{ __('messages.type_course_name_placeholder') }}"
                                                    class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:bg-white focus:border-primary-500 focus:ring-2 focus:ring-primary-100 transition-all outline-none">
                                            </div>

                                            <div class="space-y-1">
                                                <label class="text-[11px] font-bold text-slate-700 flex items-center gap-1">
                                                    {{ __('messages.school_college_institute_name') }} <span class="text-rose-500">*</span>
                                                </label>
                                                <input type="text" name="school_college" x-model="schoolCollege" required
                                                    placeholder="{{ __('messages.enter_school_name_placeholder') }}"
                                                    class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:bg-white focus:border-primary-500 focus:ring-2 focus:ring-primary-100 transition-all outline-none">
                                            </div>

                                            <!-- Single Unified Box Container for Total Marks, Obtained Marks, & Percentage -->
                                            <div class="bg-slate-50/90 border border-slate-200/80 rounded-xl p-3.5 space-y-2">
                                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                                    <div class="space-y-1">
                                                        <label class="text-[11px] font-bold text-slate-700 flex items-center gap-1">
                                                            {{ __('messages.total_marks') }} <span class="text-rose-500">*</span>
                                                        </label>
                                                        <input type="number" step="any" name="total_marks" x-model="totalMarks"
                                                            @input="calcPercentage()" @change="calcPercentage()" required
                                                            placeholder="e.g. 600"
                                                            class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg text-xs font-bold focus:border-primary-500 focus:ring-2 focus:ring-primary-100 transition-all outline-none">
                                                    </div>

                                                    <div class="space-y-1">
                                                        <label class="text-[11px] font-bold text-slate-700 flex items-center gap-1">
                                                            {{ __('messages.obtained_marks') }} <span class="text-rose-500">*</span>
                                                        </label>
                                                        <input type="number" step="any" name="received_marks"
                                                            x-model="receivedMarks" @input="calcPercentage()"
                                                            @change="calcPercentage()" required placeholder="e.g. 520"
                                                            class="w-full px-3 py-2 bg-white border border-slate-200 rounded-lg text-xs font-bold focus:border-primary-500 focus:ring-2 focus:ring-primary-100 transition-all outline-none">
                                                    </div>

                                                    <div class="space-y-1">
                                                        <label
                                                            class="text-[11px] font-bold text-slate-700 flex items-center justify-between">
                                                            <span>{{ __('messages.percentage_label') }} <span class="text-rose-500">*</span></span>
                                                            <span
                                                                class="text-[9px] text-emerald-600 font-bold bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-200/80">{{ __('messages.auto_calculated') }}</span>
                                                        </label>
                                                        <input type="text" name="percentage" x-model="percentage" readonly
                                                            tabindex="-1" required placeholder="{{ __('messages.auto_calculated_placeholder') }}"
                                                            class="w-full px-3 py-2 bg-slate-100/90 border border-slate-200 rounded-lg text-xs font-black text-slate-800 cursor-not-allowed outline-none select-none focus:ring-0">
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Marksheet File Upload Field -->
                                            <div class="space-y-1">
                                                <label
                                                    class="text-[11px] font-bold text-slate-700 flex items-center justify-between">
                                                    <span>{{ __('messages.upload_marksheet_file') }} <span class="text-rose-500"
                                                            x-show="!isEditing || !marksheetUrl">*</span></span>
                                                    <span class="text-[10px] text-slate-400 font-medium">{{ __('messages.supported_file_types') }}</span>
                                                </label>

                                                <template x-if="isEditing && marksheetUrl">
                                                    <div
                                                        class="text-[11px] font-semibold text-slate-700 bg-amber-50 p-2 rounded-lg border border-amber-200 flex items-center justify-between">
                                                        <span>{{ __('messages.current_file') }} <a :href="marksheetUrl" target="_blank"
                                                                class="font-extrabold text-primary-600 hover:underline">{{ __('messages.view_uploaded_marksheet') }} ↗</a></span>
                                                        <span class="text-[10px] text-slate-400 font-normal">{{ __('messages.upload_new_file_to_replace') }}</span>
                                                    </div>
                                                </template>

                                                <div class="flex items-center gap-2">
                                                    <input type="file" name="marksheet_file" accept="image/*,.pdf"
                                                        :required="!isEditing && !marksheetUrl"
                                                        class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-600 file:mr-3 file:py-1.5 file:px-3.5 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-primary-500 file:text-white hover:file:bg-primary-600 cursor-pointer">
                                                </div>
                                            </div>

                                            <div class="space-y-1">
                                                <label class="text-[11px] font-bold text-slate-700">{{ __('messages.special_achievement_remarks') }}</label>
                                                <textarea name="remarks" x-model="remarks" rows="2"
                                                    placeholder="{{ __('messages.remarks_placeholder') }}"
                                                    class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:bg-white focus:border-primary-500 focus:ring-2 focus:ring-primary-100 transition-all outline-none"></textarea>
                                            </div>
                                        </div>
                                    </template>
                                </div>

                            @elseif(($event->event_type ?? 'normal') === 'yuva_melo')
                                <!-- Yuva Melo Step Tabs Navigation -->
                                <div class="flex border-b border-slate-200/80 mb-2.5 gap-2 overflow-x-auto pb-1">
                                    <button type="button" @click="yuvaTab = 1"
                                        :class="yuvaTab === 1 ? 'border-primary-500 text-primary-600 font-extrabold bg-primary-50/50' : 'border-transparent text-slate-500 hover:text-slate-700'"
                                        class="px-4 py-2 border-b-2 text-xs transition-all whitespace-nowrap rounded-t-lg cursor-pointer">
                                        {{ __('messages.yuva_tab_1') }}
                                    </button>
                                    <button type="button" @click="yuvaTab = 2"
                                        :class="yuvaTab === 2 ? 'border-primary-500 text-primary-600 font-extrabold bg-primary-50/50' : 'border-transparent text-slate-500 hover:text-slate-700'"
                                        class="px-4 py-2 border-b-2 text-xs transition-all whitespace-nowrap rounded-t-lg cursor-pointer">
                                        {{ __('messages.yuva_tab_2') }}
                                    </button>
                                    <button type="button" @click="yuvaTab = 3"
                                        :class="yuvaTab === 3 ? 'border-primary-500 text-primary-600 font-extrabold bg-primary-50/50' : 'border-transparent text-slate-500 hover:text-slate-700'"
                                        class="px-4 py-2 border-b-2 text-xs transition-all whitespace-nowrap rounded-t-lg cursor-pointer">
                                        {{ __('messages.yuva_tab_3') }}
                                    </button>
                                </div>

                                <!-- STEP 1: Candidate's Info -->
                                <div x-show="yuvaTab === 1" class="space-y-2.5 text-xs">
                                    <div
                                        class="bg-slate-50/80 px-3 py-2 rounded-xl border border-slate-100 font-bold text-primary-800 text-[11px]">
                                        {{ __('messages.yuva_sec_1') }}
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3.5">
                                        <div class="space-y-1">
                                            <label class="text-[11px] font-bold text-slate-700">{{ __('messages.surname') }} <span
                                                    class="text-rose-500">*</span></label>
                                            <input type="text" name="surname" value="{{ old('surname') }}" required
                                                placeholder="Enter surname"
                                                class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs font-semibold focus:bg-white focus:border-primary-500 outline-none">
                                        </div>
                                        <div class="space-y-1">
                                            <label class="text-[11px] font-bold text-slate-700">{{ __('messages.first_name') }}
                                                <span class="text-rose-500">*</span></label>
                                            <input type="text" name="first_name" value="{{ old('first_name') }}" required
                                                placeholder="Enter first name"
                                                class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs font-semibold focus:bg-white focus:border-primary-500 outline-none">
                                        </div>
                                        <div class="space-y-1">
                                            <label class="text-[11px] font-bold text-slate-700">{{ __('messages.gender') }} <span
                                                    class="text-rose-500">*</span></label>
                                            <select name="gender" required
                                                class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs font-semibold focus:bg-white focus:border-primary-500 outline-none">
                                                <option value="" disabled {{ old('gender') ? '' : 'selected' }}>Select Gender
                                                </option>
                                                <option value="Male" {{ old('gender') === 'Male' ? 'selected' : '' }}>Male</option>
                                                <option value="Female" {{ old('gender') === 'Female' ? 'selected' : '' }}>Female
                                                </option>
                                                <option value="Other" {{ old('gender') === 'Other' ? 'selected' : '' }}>Other</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-4 gap-3.5">
                                        <div class="space-y-1">
                                            <label class="text-[11px] font-bold text-slate-700">{{ __('messages.birth_date') }}
                                                <span class="text-rose-500">*</span></label>
                                            <input type="date" name="birth_date" value="{{ old('birth_date') }}" required
                                                class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs font-semibold focus:bg-white focus:border-primary-500 outline-none">
                                        </div>
                                        <div class="space-y-1">
                                            <label class="text-[11px] font-bold text-slate-700">{{ __('messages.age') }} <span
                                                    class="text-rose-500">*</span></label>
                                            <input type="number" name="age" value="{{ old('age') }}" required placeholder="e.g. 25"
                                                class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs font-semibold focus:bg-white focus:border-primary-500 outline-none">
                                        </div>
                                        <div class="space-y-1">
                                            <label class="text-[11px] font-bold text-slate-700">{{ __('messages.height') }}</label>
                                            <input type="text" name="height" value="{{ old('height') }}"
                                                placeholder="e.g. 5'6\"" class=" w-full px-3 py-2 bg-slate-50 border
                                                border-slate-200 rounded-lg text-xs font-semibold focus:bg-white
                                                focus:border-primary-500 outline-none">
                                        </div>
                                        <div class="space-y-1">
                                            <label class="text-[11px] font-bold text-slate-700">{{ __('messages.weight') }}</label>
                                            <input type="text" name="weight" value="{{ old('weight') }}" placeholder="e.g. 60 kg"
                                                class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs font-semibold focus:bg-white focus:border-primary-500 outline-none">
                                        </div>
                                    </div>

                                    <div class="space-y-1">
                                        <label class="text-[11px] font-bold text-slate-700">{{ __('messages.address') }} <span
                                                class="text-rose-500">*</span></label>
                                        <textarea name="address" rows="2" required placeholder="Enter full address"
                                            class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs font-semibold focus:bg-white focus:border-primary-500 outline-none">{{ old('address') }}</textarea>
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3.5">
                                        <div class="space-y-1">
                                            <label class="text-[11px] font-bold text-slate-700">{{ __('messages.state') }} <span
                                                    class="text-rose-500">*</span></label>
                                            <input type="text" name="state" value="{{ old('state', 'Gujarat') }}" required
                                                class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs font-semibold focus:bg-white focus:border-primary-500 outline-none">
                                        </div>
                                        <div class="space-y-1">
                                            <label class="text-[11px] font-bold text-slate-700">{{ __('messages.district') }} <span
                                                    class="text-rose-500">*</span></label>
                                            <input type="text" name="district" value="{{ old('district') }}" required
                                                placeholder="Enter district"
                                                class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs font-semibold focus:bg-white focus:border-primary-500 outline-none">
                                        </div>
                                        <div class="space-y-1">
                                            <label class="text-[11px] font-bold text-slate-700">Area <span
                                                    class="text-rose-500">*</span></label>
                                            <select name="area_id" required
                                                class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs font-semibold focus:bg-white focus:border-primary-500 outline-none">
                                                <option value="">-- Select Area --</option>
                                                @if(isset($areas))
                                                    @foreach($areas as $areaItem)
                                                        <option value="{{ $areaItem->id }}" {{ old('area_id') == $areaItem->id ? 'selected' : '' }}>
                                                            {{ $areaItem->name }}{{ $areaItem->pincode ? ' (' . $areaItem->pincode . ')' : '' }}
                                                        </option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                        <div class="space-y-1">
                                            <label
                                                class="text-[11px] font-bold text-slate-700">{{ __('messages.association') }}</label>
                                            <input type="text" name="association" value="{{ old('association') }}"
                                                placeholder="Enter mandal/association"
                                                class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs font-semibold focus:bg-white focus:border-primary-500 outline-none">
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                                        <div class="space-y-1">
                                            <label class="text-[11px] font-bold text-slate-700">{{ __('messages.mobile') }} <span
                                                    class="text-rose-500">*</span></label>
                                            <input type="text" name="mobile_no"
                                                value="{{ old('mobile_no', auth()->user()->memberProfile->phone ?? '') }}" required
                                                maxlength="10" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10)"
                                                placeholder="10-digit number"
                                                class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs font-semibold focus:bg-white focus:border-primary-500 outline-none">
                                        </div>
                                        <div class="space-y-1">
                                            <label
                                                class="text-[11px] font-bold text-slate-700">{{ __('messages.whatsapp_no') }}</label>
                                            <input type="text" name="whatsapp" value="{{ old('whatsapp') }}" maxlength="10"
                                                oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10)"
                                                placeholder="10-digit whatsapp number"
                                                class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs font-semibold focus:bg-white focus:border-primary-500 outline-none">
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3.5">
                                        <div class="space-y-1">
                                            <label class="text-[11px] font-bold text-slate-700">{{ __('messages.qualification') }}
                                                <span class="text-rose-500">*</span></label>
                                            <input type="text" name="qualification" value="{{ old('qualification') }}" required
                                                placeholder="e.g. Graduate / B.E."
                                                class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs font-semibold focus:bg-white focus:border-primary-500 outline-none">
                                        </div>
                                        <div class="space-y-1">
                                            <label class="text-[11px] font-bold text-slate-700">{{ __('messages.occupation') }}
                                                <span class="text-rose-500">*</span></label>
                                            <input type="text" name="occupation" value="{{ old('occupation') }}" required
                                                placeholder="e.g. Job / Business"
                                                class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs font-semibold focus:bg-white focus:border-primary-500 outline-none">
                                        </div>
                                        <div class="space-y-1">
                                            <label
                                                class="text-[11px] font-bold text-slate-700">{{ __('messages.monthly_income') }}</label>
                                            <input type="text" name="monthly_income" value="{{ old('monthly_income') }}"
                                                placeholder="e.g. Rs. 25,000"
                                                class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs font-semibold focus:bg-white focus:border-primary-500 outline-none">
                                        </div>
                                    </div>

                                    <div class="space-y-1">
                                        <label
                                            class="text-[11px] font-bold text-slate-700">{{ __('messages.occupation_address') }}</label>
                                        <input type="text" name="occupation_address" value="{{ old('occupation_address') }}"
                                            placeholder="Enter job/business address"
                                            class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs font-semibold focus:bg-white focus:border-primary-500 outline-none">
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                                        <div class="space-y-1">
                                            <label class="text-[11px] font-bold text-slate-700">{{ __('messages.divorce_status') }}
                                                <span class="text-rose-500">*</span></label>
                                            <select name="divorce" required
                                                class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs font-semibold focus:bg-white focus:border-primary-500 outline-none">
                                                <option value="No" {{ old('divorce', 'No') === 'No' ? 'selected' : '' }}>No (ના)
                                                </option>
                                                <option value="Yes" {{ old('divorce') === 'Yes' ? 'selected' : '' }}>Yes (હા)</option>
                                            </select>
                                        </div>
                                        <div class="space-y-1">
                                            <label
                                                class="text-[11px] font-bold text-slate-700">{{ __('messages.special_need') }}</label>
                                            <input type="text" name="special_need" value="{{ old('special_need') }}"
                                                placeholder="e.g. None / Physical Disability details"
                                                class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs font-semibold focus:bg-white focus:border-primary-500 outline-none">
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                                        <div class="space-y-1">
                                            <label
                                                class="text-[11px] font-bold text-slate-700">{{ __('messages.physical_disability') }}</label>
                                            <input type="text" name="physical_disability" value="{{ old('physical_disability') }}"
                                                placeholder="e.g. None / Details"
                                                class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs font-semibold focus:bg-white focus:border-primary-500 outline-none">
                                        </div>
                                        <div class="space-y-1">
                                            <label
                                                class="text-[11px] font-bold text-slate-700">{{ __('messages.disability_duration') }}</label>
                                            <input type="text" name="disability_duration" value="{{ old('disability_duration') }}"
                                                placeholder="e.g. Since birth / N/A"
                                                class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs font-semibold focus:bg-white focus:border-primary-500 outline-none">
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                                        <div class="space-y-1">
                                            <label
                                                class="text-[11px] font-bold text-slate-700">{{ __('messages.special_info') }}</label>
                                            <input type="text" name="special_info" value="{{ old('special_info') }}"
                                                placeholder="Any special achievement or information"
                                                class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs font-semibold focus:bg-white focus:border-primary-500 outline-none">
                                        </div>
                                        <div class="space-y-1">
                                            <label
                                                class="text-[11px] font-bold text-slate-700">{{ __('messages.other_info') }}</label>
                                            <input type="text" name="other_info" value="{{ old('other_info') }}"
                                                placeholder="Additional notes or remarks"
                                                class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs font-semibold focus:bg-white focus:border-primary-500 outline-none">
                                        </div>
                                    </div>

                                    <!-- Document Upload Grid -->
                                    <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                                        <div class="bg-slate-50/50 p-2.5 rounded-xl border border-slate-200/60 space-y-1">
                                            <label
                                                class="text-[10px] font-bold text-slate-600 block">{{ __('messages.member_photo') }}</label>
                                            <input type="file" name="member_photo" accept="image/*"
                                                class="w-full text-[10px] text-slate-500 file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:text-[10px] file:font-bold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100 cursor-pointer">
                                        </div>
                                        <div class="bg-slate-50/50 p-2.5 rounded-xl border border-slate-200/60 space-y-1">
                                            <label
                                                class="text-[10px] font-bold text-slate-600 block">{{ __('messages.aadhaar_photo') }}</label>
                                            <input type="file" name="aadhaar_photo" accept="image/*"
                                                class="w-full text-[10px] text-slate-500 file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:text-[10px] file:font-bold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100 cursor-pointer">
                                        </div>
                                        <div class="bg-slate-50/50 p-2.5 rounded-xl border border-slate-200/60 space-y-1">
                                            <label
                                                class="text-[10px] font-bold text-slate-600 block">{{ __('messages.selfie') }}</label>
                                            <input type="file" name="selfie" accept="image/*"
                                                class="w-full text-[10px] text-slate-500 file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:text-[10px] file:font-bold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100 cursor-pointer">
                                        </div>
                                        <div class="bg-slate-50/50 p-2.5 rounded-xl border border-slate-200/60 space-y-1">
                                            <label
                                                class="text-[10px] font-bold text-slate-600 block">{{ __('messages.whatsapp_image') }}</label>
                                            <input type="file" name="whatsapp_image" accept="image/*"
                                                class="w-full text-[10px] text-slate-500 file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:text-[10px] file:font-bold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100 cursor-pointer">
                                        </div>
                                    </div>

                                    <!-- Tab 1 Next Button -->
                                    <div class="flex justify-end pt-2">
                                        <button type="button" @click="yuvaTab = 2"
                                            class="px-5 py-2 bg-primary-500 text-white hover:bg-primary-600 font-bold text-xs rounded-xl shadow-xs transition-colors cursor-pointer">
                                            {!! __('messages.next_step') !!}
                                        </button>
                                    </div>
                                </div>

                                <!-- STEP 2: Father & Family Info -->
                                <div x-show="yuvaTab === 2" class="space-y-2.5 text-xs" x-cloak>
                                    <div
                                        class="bg-slate-50/80 px-3 py-2 rounded-xl border border-slate-100 font-bold text-primary-800 text-[11px]">
                                        {{ __('messages.yuva_sec_2') }}
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                                        <div class="space-y-1">
                                            <label class="text-[11px] font-bold text-slate-700">{{ __('messages.father_name') }}
                                                <span class="text-rose-500">*</span></label>
                                            <input type="text" name="father_name" value="{{ old('father_name') }}" required
                                                placeholder="Enter father's full name"
                                                class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs font-semibold focus:bg-white focus:border-primary-500 outline-none">
                                        </div>
                                        <div class="space-y-1">
                                            <label
                                                class="text-[11px] font-bold text-slate-700">{{ __('messages.grandfather_name') }}
                                                <span class="text-rose-500">*</span></label>
                                            <input type="text" name="grandfather_name" value="{{ old('grandfather_name') }}"
                                                required placeholder="Enter grandfather's full name"
                                                class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs font-semibold focus:bg-white focus:border-primary-500 outline-none">
                                        </div>
                                        {{--
                                        <div class="space-y-1">
                                            <label class="text-[11px] font-bold text-slate-700">Father's Gyanti (Gnati)</label>
                                            <input type="text" name="father_gyanti" value="{{ old('father_gyanti') }}"
                                                placeholder="e.g. Satwara / Patel"
                                                class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs font-semibold focus:bg-white focus:border-primary-500 outline-none">
                                        </div>
                                        --}}
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3.5">
                                        <div class="space-y-1">
                                            <label
                                                class="text-[11px] font-bold text-slate-700">{{ __('messages.father_age') }}</label>
                                            <input type="number" name="father_age" value="{{ old('father_age') }}"
                                                placeholder="e.g. 52"
                                                class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs font-semibold focus:bg-white focus:border-primary-500 outline-none">
                                        </div>
                                        <div class="space-y-1">
                                            <label
                                                class="text-[11px] font-bold text-slate-700">{{ __('messages.father_occupation') }}</label>
                                            <input type="text" name="father_occupation" value="{{ old('father_occupation') }}"
                                                placeholder="e.g. Farming / Business"
                                                class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs font-semibold focus:bg-white focus:border-primary-500 outline-none">
                                        </div>
                                        <div class="space-y-1">
                                            <label
                                                class="text-[11px] font-bold text-slate-700">{{ __('messages.father_income') }}</label>
                                            <input type="text" name="father_income" value="{{ old('father_income') }}"
                                                placeholder="Annual or Monthly Income"
                                                class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs font-semibold focus:bg-white focus:border-primary-500 outline-none">
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                                        <div class="space-y-1">
                                            <label
                                                class="text-[11px] font-bold text-slate-700">{{ __('messages.father_occupation_address') }}</label>
                                            <input type="text" name="father_occupation_address"
                                                value="{{ old('father_occupation_address') }}"
                                                placeholder="Enter father's job/business address"
                                                class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs font-semibold focus:bg-white focus:border-primary-500 outline-none">
                                        </div>
                                        <div class="space-y-1">
                                            <label
                                                class="text-[11px] font-bold text-slate-700">{{ __('messages.father_mobile') }}</label>
                                            <input type="text" name="father_mobile" value="{{ old('father_mobile') }}"
                                                maxlength="10" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10)"
                                                placeholder="10-digit mobile number"
                                                class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs font-semibold focus:bg-white focus:border-primary-500 outline-none">
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3.5">
                                        <div class="space-y-1">
                                            <label class="text-[11px] font-bold text-slate-700">{{ __('messages.mother_name') }}
                                                <span class="text-rose-500">*</span></label>
                                            <input type="text" name="mother_name" value="{{ old('mother_name') }}" required
                                                placeholder="Enter mother's name"
                                                class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs font-semibold focus:bg-white focus:border-primary-500 outline-none">
                                        </div>
                                        {{--
                                        <div class="space-y-1">
                                            <label class="text-[11px] font-bold text-slate-700">Mother's Gyanti (Gnati)</label>
                                            <input type="text" name="mother_gyanti" value="{{ old('mother_gyanti') }}"
                                                placeholder="e.g. Satwara / Patel"
                                                class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs font-semibold focus:bg-white focus:border-primary-500 outline-none">
                                        </div>
                                        --}}
                                        <div class="space-y-1">
                                            <label
                                                class="text-[11px] font-bold text-slate-700">{{ __('messages.mother_occupation') }}</label>
                                            <input type="text" name="mother_occupation"
                                                value="{{ old('mother_occupation', 'Housewife') }}" placeholder="e.g. Housewife"
                                                class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs font-semibold focus:bg-white focus:border-primary-500 outline-none">
                                        </div>
                                        <div class="space-y-1">
                                            <label class="text-[11px] font-bold text-slate-700">{{ __('messages.native_place') }}
                                                <span class="text-rose-500">*</span></label>
                                            <input type="text" name="native_place" value="{{ old('native_place') }}" required
                                                placeholder="Enter native village/city"
                                                class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs font-semibold focus:bg-white focus:border-primary-500 outline-none">
                                        </div>
                                    </div>

                                    <!-- Brother and Sister Details (Siblings Section with Modal + Cards) -->
                                    <div class="bg-slate-50/80 p-3.5 rounded-2xl border border-slate-200/80 space-y-2.5">
                                        <div class="flex items-center justify-between flex-wrap gap-2">
                                            <div>
                                                <h4 class="font-extrabold text-xs text-slate-800 uppercase tracking-wider">
                                                    {{ __('messages.siblings_details') }}
                                                </h4>
                                                <p class="text-[10px] text-slate-400 mt-0.5">Add brother(s) and sister(s) details
                                                    using
                                                    the button</p>
                                            </div>
                                            <button type="button" @click="showSiblingModal = true"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-primary-600 hover:bg-primary-700 text-white font-bold text-xs rounded-xl shadow-xs transition-all cursor-pointer">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                                        d="M12 4v16m8-8H4"></path>
                                                </svg>
                                                <span>Add Sibling</span>
                                            </button>
                                        </div>

                                        <!-- Sibling Cards List (Compact Small Cards) -->
                                        <div class="grid grid-cols-1 sm:grid-cols-3 md:grid-cols-4 gap-2 pt-1"
                                            x-show="siblings.length > 0">
                                            <template x-for="(s, index) in siblings" :key="index">
                                                <div
                                                    class="bg-white p-2 rounded-lg border border-slate-200 shadow-2xs flex items-center justify-between min-w-0">
                                                    <div class="min-w-0 space-y-0.5">
                                                        <div class="flex items-center gap-1.5 truncate">
                                                            <span
                                                                class="text-[9px] font-black px-1.5 py-0.5 rounded-md text-white tracking-tight shrink-0"
                                                                :class="{
                                                                                      'bg-blue-600': s.relation.includes('Brother'),
                                                                                      'bg-pink-600': s.relation.includes('Sister')
                                                                                  }" x-text="s.relation"></span>
                                                            <span class="text-[11px] font-extrabold text-slate-800 truncate"
                                                                x-text="s.details || '1 Member'"></span>
                                                        </div>
                                                        <div
                                                            class="flex items-center gap-1 text-[10px] text-slate-500 font-medium truncate">
                                                            <span x-text="s.married === 'Yes' ? 'Married' : 'Unmarried'"></span>
                                                            <template x-if="s.occupation">
                                                                <span class="truncate" x-text="'• ' + s.occupation"></span>
                                                            </template>
                                                        </div>
                                                    </div>
                                                    <button type="button" @click="removeSibling(index)"
                                                        class="p-1 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded transition-colors shrink-0 ml-1"
                                                        title="Remove Sibling">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                            </path>
                                                        </svg>
                                                    </button>
                                                </div>
                                            </template>
                                        </div>

                                        <!-- Empty State -->
                                        <div x-show="siblings.length === 0"
                                            class="p-3 border border-dashed border-slate-200 rounded-xl text-center text-xs text-slate-400 font-semibold bg-white/60">
                                            No siblings added yet. Click <strong class="text-primary-600">"+ Add Sibling"</strong>
                                            to
                                            add details.
                                        </div>

                                        <!-- Hidden Sync Fields -->
                                        <input type="hidden" name="siblings_json" :value="JSON.stringify(siblings)">
                                        <input type="hidden" name="elder_brother" :value="legacyElderB">
                                        <input type="hidden" name="elder_brother_married" :value="legacyElderBM">
                                        <input type="hidden" name="younger_brother" :value="legacyYoungerB">
                                        <input type="hidden" name="younger_brother_married" :value="legacyYoungerBM">
                                        <input type="hidden" name="elder_sister" :value="legacyElderS">
                                        <input type="hidden" name="elder_sister_married" :value="legacyElderSM">
                                        <input type="hidden" name="younger_sister" :value="legacyYoungerS">
                                        <input type="hidden" name="younger_sister_married" :value="legacyYoungerSM">
                                    </div>

                                    <!-- Family Business, Property, Vehicle Info -->
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                                        <div class="space-y-1">
                                            <label
                                                class="text-[11px] font-bold text-slate-700">{{ __('messages.business_details') }}</label>
                                            <input type="text" name="business" value="{{ old('business') }}"
                                                placeholder="Family business info"
                                                class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs font-semibold focus:bg-white focus:border-primary-500 outline-none">
                                        </div>
                                        <div class="space-y-1">
                                            <label
                                                class="text-[11px] font-bold text-slate-700">{{ __('messages.house_type') }}</label>
                                            <input type="text" name="house" value="{{ old('house') }}"
                                                placeholder="e.g. Flat / Tenement"
                                                class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs font-semibold focus:bg-white focus:border-primary-500 outline-none">
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                                        <div class="space-y-1">
                                            <label
                                                class="text-[11px] font-bold text-slate-700">{{ __('messages.own_house') }}</label>
                                            <select name="own_house"
                                                class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs font-semibold focus:bg-white focus:border-primary-500 outline-none">
                                                <option value="Yes" {{ old('own_house') === 'Yes' ? 'selected' : '' }}>Yes</option>
                                                <option value="No" {{ old('own_house', 'No') === 'No' ? 'selected' : '' }}>No</option>
                                            </select>
                                        </div>
                                        <div class="space-y-1">
                                            <label
                                                class="text-[11px] font-bold text-slate-700">{{ __('messages.vehicle_details') }}</label>
                                            <input type="text" name="vehicle" value="{{ old('vehicle') }}"
                                                placeholder="e.g. Two Wheeler / Car model"
                                                class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs font-semibold focus:bg-white focus:border-primary-500 outline-none">
                                        </div>
                                    </div>

                                    <!-- Tab 2 Next/Prev Buttons -->
                                    <div class="flex justify-between pt-2">
                                        <button type="button" @click="yuvaTab = 1"
                                            class="px-4 py-2 border border-slate-200 hover:bg-slate-50 font-bold text-xs rounded-xl transition-colors cursor-pointer">
                                            {!! __('messages.prev_step') !!}
                                        </button>
                                        <button type="button" @click="yuvaTab = 3"
                                            class="px-5 py-2 bg-primary-500 text-white hover:bg-primary-600 font-bold text-xs rounded-xl shadow-xs transition-colors cursor-pointer">
                                            {!! __('messages.next_step') !!}
                                        </button>
                                    </div>
                                </div>

                                <!-- STEP 3: Maternal Info -->
                                <div x-show="yuvaTab === 3" class="space-y-2.5 text-xs" x-cloak>
                                    <div
                                        class="bg-slate-50/80 px-3 py-2 rounded-xl border border-slate-100 font-bold text-primary-800 text-[11px]">
                                        {{ __('messages.yuva_sec_3') }}
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                                        <div class="space-y-1">
                                            <label
                                                class="text-[11px] font-bold text-slate-700">{{ __('messages.maternal_uncle_name') }}
                                                <span class="text-rose-500">*</span></label>
                                            <input type="text" name="maternal_uncle_name" value="{{ old('maternal_uncle_name') }}"
                                                required placeholder="Enter maternal uncle's name"
                                                class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs font-semibold focus:bg-white focus:border-primary-500 outline-none">
                                        </div>
                                        <div class="space-y-1">
                                            <label
                                                class="text-[11px] font-bold text-slate-700">{{ __('messages.maternal_grandfather_name') }}
                                                <span class="text-rose-500">*</span></label>
                                            <input type="text" name="maternal_grandfather_name"
                                                value="{{ old('maternal_grandfather_name') }}" required
                                                placeholder="Enter maternal grandfather's name"
                                                class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs font-semibold focus:bg-white focus:border-primary-500 outline-none">
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                                        <div class="space-y-1">
                                            <label
                                                class="text-[11px] font-bold text-slate-700">{{ __('messages.maternal_grandfather_address') }}</label>
                                            <input type="text" name="maternal_grandfather_address"
                                                value="{{ old('maternal_grandfather_address') }}"
                                                placeholder="Enter maternal grandfather address"
                                                class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs font-semibold focus:bg-white focus:border-primary-500 outline-none">
                                        </div>
                                        <div class="space-y-1">
                                            <label
                                                class="text-[11px] font-bold text-slate-700">{{ __('messages.maternal_grandfather_occupation') }}</label>
                                            <input type="text" name="maternal_grandfather_occupation"
                                                value="{{ old('maternal_grandfather_occupation') }}"
                                                placeholder="e.g. Farming / Retired"
                                                class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs font-semibold focus:bg-white focus:border-primary-500 outline-none">
                                        </div>
                                    </div>

                                    <!-- Member Verification Section -->
                                    <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200/80 space-y-2">
                                        <h4 class="font-extrabold text-[11px] text-slate-600 uppercase tracking-wider">{{ __('messages.member_number') }}</h4>
                                        <div class="space-y-1">
                                            <label class="text-[11px] font-bold text-slate-700">{{ __('messages.member_number') }}</label>
                                            <input type="text" name="member_number"
                                                value="{{ old('member_number', auth()->check() ? '#' . sprintf('%05d', auth()->user()->id) : '') }}"
                                                placeholder="e.g. #00005"
                                                @if(auth()->check()) readonly @endif
                                                class="w-full px-3 py-2 {{ auth()->check() ? 'bg-slate-100 text-slate-800 font-extrabold cursor-not-allowed select-none border-slate-200' : 'bg-white border-slate-200 focus:border-primary-500' }} rounded-lg text-xs outline-none">
                                        </div>
                                    </div>

                                    @if(($event->form_fee ?? 0) > 0)
                                        <div class="p-3.5 bg-primary-50/80 border border-primary-200 rounded-xl flex items-center justify-between shadow-2xs">
                                            <div class="flex items-center gap-2.5">
                                                <span class="w-8 h-8 rounded-xl bg-primary-600 text-white flex items-center justify-center text-sm font-bold shadow-xs">💳</span>
                                                <div>
                                                    <h4 class="text-xs font-black text-slate-900">{{ app()->getLocale() === 'gu' ? 'યુવા બાયોડેટા નોંધણી ફી' : 'Youth Biodata Registration Fee' }}</h4>
                                                    <p class="text-[10px] text-slate-500 font-medium">{{ app()->getLocale() === 'gu' ? 'આ અરજી સબમિટ કરતી વખતે ઓનલાઇન પેમેન્ટ જરૂરી છે' : 'Online payment required upon submitting this application' }}</p>
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block">{{ __('messages.form_fee') }}</span>
                                                <span class="text-base font-black text-primary-600">₹{{ number_format($event->form_fee, 0) }}</span>
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Tab 3 Prev Button -->
                                    <div class="flex justify-start pt-2">
                                        <button type="button" @click="yuvaTab = 2"
                                            class="px-4 py-2 border border-slate-200 hover:bg-slate-50 font-bold text-xs rounded-xl transition-colors cursor-pointer">
                                            {!! __('messages.prev_step') !!}
                                        </button>
                                    </div>
                                </div>

                            @else
                                <!-- Normal Event Form Fields -->
                                <div class="space-y-3.5 text-xs">
                                    <div class="space-y-1">
                                        <label class="text-[11px] font-bold text-slate-700 flex items-center gap-1">
                                            Participant Full Name <span class="text-rose-500">*</span>
                                        </label>
                                        <input type="text" name="full_name"
                                            value="{{ old('full_name', auth()->check() ? auth()->user()->name : '') }}" required
                                            class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs font-semibold focus:bg-white focus:border-primary-500 focus:ring-2 focus:ring-primary-100 transition-all outline-none">
                                    </div>

                                    <div class="space-y-1">
                                        <label class="text-[11px] font-bold text-slate-700 flex items-center gap-1">
                                            Contact Number <span class="text-rose-500">*</span>
                                        </label>
                                        <input type="text" name="contact_number"
                                            value="{{ old('contact_number', auth()->check() ? (auth()->user()->memberProfile->phone ?? '') : '') }}"
                                            required maxlength="10"
                                            oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10)"
                                            placeholder="Enter 10-digit mobile number"
                                            class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs font-semibold focus:bg-white focus:border-primary-500 focus:ring-2 focus:ring-primary-100 transition-all outline-none">
                                    </div>

                                    <!-- Person Counter (+ Person -) -->
                                    @php
                                        $alreadyPurchasedPasses = (isset($registration) && !empty($registration->form_data['person_count'])) ? (int)$registration->form_data['person_count'] : 0;
                                        $maxPassesRemaining = !empty($event->total_pass_limit) ? max(0, $event->total_pass_limit - $event->total_passes_count) : null;
                                        $initialCount = (int) old('person_count', $alreadyPurchasedPasses > 0 ? $alreadyPurchasedPasses : 1);
                                        if ($maxPassesRemaining !== null) {
                                            $initialCount = min(max(1, $initialCount), max(1, $maxPassesRemaining));
                                        }
                                    @endphp
                                    <div x-data="{ count: {{ $initialCount }}, maxAllowed: {{ $maxPassesRemaining ?? 'null' }} }" class="space-y-1.5 pt-1">
                                        <label class="text-[11px] font-bold text-slate-700 flex items-center justify-between">
                                            <span>{{ __('messages.ketla_person_attending') }} <span class="text-rose-500">*</span></span>
                                        </label>
                                        <div class="flex items-center justify-between bg-slate-100/90 p-1.5 rounded-xl border border-slate-200/80">
                                            <button type="button"
                                                    @click="if (count > 1) count--"
                                                    :disabled="count <= 1"
                                                    class="w-9 h-9 rounded-lg bg-white hover:bg-slate-200 active:scale-95 disabled:opacity-40 disabled:cursor-not-allowed border border-slate-200 text-slate-800 font-black text-lg flex items-center justify-center transition-all cursor-pointer shadow-xs">
                                                &minus;
                                            </button>

                                            <div class="flex items-center gap-1.5 px-3">
                                                <span class="text-base font-black text-primary-600" x-text="count"></span>
                                                <span class="text-xs font-extrabold text-slate-700 uppercase tracking-wider" x-text="count > 1 ? '{{ __('messages.persons') }}' : '{{ __('messages.person') }}'"></span>
                                            </div>

                                            <button type="button"
                                                    @click="if (!maxAllowed || count < maxAllowed) count++"
                                                    :disabled="maxAllowed && count >= maxAllowed"
                                                    class="w-9 h-9 rounded-lg bg-primary-500 hover:bg-primary-600 active:scale-95 disabled:opacity-40 disabled:cursor-not-allowed text-white font-black text-lg flex items-center justify-center transition-all cursor-pointer shadow-xs">
                                                &#43;
                                            </button>
                                        </div>
                                        @if($maxPassesRemaining !== null)
                                        <p class="text-[10px] font-semibold text-amber-600 flex items-center gap-1">
                                            <span x-show="count >= maxAllowed">{{ __('messages.max_passes_reached_hint') }}</span>
                                            <span x-show="count < maxAllowed">{{ __('messages.max_passes_remaining_hint', ['count' => $maxPassesRemaining]) }}</span>
                                        </p>
                                        @endif
                                        <input type="hidden" name="person_count" :value="count">
                                    </div>

                                    <div class="space-y-1">
                                        <label class="text-[11px] font-bold text-slate-700">Special Notes / Remarks
                                            (Optional)</label>
                                        <textarea name="remarks" rows="2"
                                            placeholder="Any special seating or assistance requirements"
                                            class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs font-semibold focus:bg-white focus:border-primary-500 focus:ring-2 focus:ring-primary-100 transition-all outline-none">{{ old('remarks') }}</textarea>
                                    </div>
                                </div>
                            @endif

                            <!-- ACTION BUTTONS -->
                            <div x-show="(('{{ $event->event_type ?? 'normal' }}' !== 'inam_vitaran') || selectedStudent !== '') && ('{{ $event->event_type ?? 'normal' }}' !== 'yuva_melo' || yuvaTab === 3)"
                                class="pt-3 border-t border-slate-100 flex items-center justify-end gap-3">
                                @if(auth()->check())
                                    <a href="{{ route('member.events.index') }}"
                                        class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition-colors">
                                        {{ __('messages.cancel') }}
                                    </a>
                                @endif
                                <button type="submit"
                                    class="px-6 py-2.5 bg-primary-500 hover:bg-primary-600 text-white font-extrabold text-xs rounded-xl shadow-xs transition-all transform active:scale-95 flex items-center gap-2 uppercase tracking-wider cursor-pointer">
                                    <span>{{ __('messages.submit_registration') }}</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"></path>
                                    </svg>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif
            </div> <!-- END TAB 1: NEW REGISTRATION FORM -->

            @if(isset($registrations) && $registrations->count() > 0)
                <!-- ================= TAB 2: SUBMITTED REGISTRATION DETAILS ================= -->
                <div x-show="mainPageTab === 'submitted'" x-cloak class="space-y-3 pt-1">
                    @if($isRegistrationClosed)
                        <div class="p-3 bg-rose-50 border border-rose-200 rounded-xl text-xs text-rose-800 flex items-center gap-2 font-bold">
                            <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>
                                @if($isRegistrationFormDisabled)
                                    Registration form is disabled for this event.@if(!empty($event->form_end_date)) Form fill-up closed on {{ date('d-M-Y', strtotime($event->form_end_date)) }}.@endif
                                @elseif($formNotYetOpen)
                                    Form fill-up has not started. It opens on {{ date('d-M-Y', strtotime($event->form_start_date)) }}.
                                @else
                                    Form fill-up closed on {{ date('d-M-Y', strtotime($event->form_end_date)) }}. You can view your previous submissions below.
                                @endif
                            </span>
                        </div>
                    @endif
                    <div class="flex items-center justify-between px-1">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                            <h3 class="text-xs font-black text-slate-800 uppercase tracking-wider">
                                {{ __('messages.submitted_registration_details') }} ({{ $registrations->count() }})
                            </h3>
</div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">{{ __('messages.click_card_to_view_full') }}</span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($registrations as $index => $reg)
                            @if(!empty($reg->form_data) && (($event->event_type ?? 'normal') !== 'inam_vitaran' || !empty($reg->form_data['student_name'])))
                                    @php 
                                        $fd = $reg->form_data;
                                        $cardIndex = $registrations->count() - $index;
                                        $regNo = $reg->reference_number ? sprintf('%03d', $reg->reference_number) : (isset($fd['registration_no']) && is_numeric($fd['registration_no']) ? sprintf('%03d', (int)$fd['registration_no']) : sprintf('%03d', $cardIndex));
                                        $isYuva = ($event->event_type ?? 'normal') === 'yuva_melo';
                                    @endphp
                                    <div @if($isYuva) @click="selectedRegistration = {{ json_encode([
                                            'id' => $reg->id,
                                            'index' => $cardIndex,
                                            'reg_no' => $regNo,
                                            'date' => $fd['submission_date'] ?? ($reg->created_at ? $reg->created_at->format('d-M-Y h:i A') : '-'),
                                            'status' => $reg->status,
                                            'payment_status' => $reg->payment_status ?? 'unpaid',
                                            'payment_amount' => $reg->payment_amount ?? 0,
                                            'form_data' => $fd
                                        ]) }}; showDetailsModal = true" @endif
                                        class="bg-white border border-slate-200/90 rounded-xl p-4 space-y-3 shadow-xs {{ $isYuva ? 'hover:shadow-md hover:border-primary-400 transition-all cursor-pointer group' : '' }}">

                                        <!-- Card Header: Reference No & Candidate Name -->
                                        <div class="flex items-center justify-between border-b border-slate-100 pb-2.5">
                                            <div class="flex items-center gap-1.5 min-w-0">
                                                <span class="px-1.5 py-0.5 bg-slate-100 text-slate-700 rounded font-mono font-bold text-[10px]">#{{ $regNo }}</span>
                                                <h4
                                                    class="text-xs font-black text-slate-900 truncate {{ $isYuva ? 'group-hover:text-primary-600 transition-colors' : '' }}">
                                                    {{ $fd['full_name'] ?? $fd['student_name'] ?? __('messages.registration') }}
                                                </h4>
                                            </div>
                                            <span
                                                class="text-[9px] font-extrabold px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 border border-emerald-200 uppercase">
                                                {{ __('messages.submitted') }}
                                            </span>
                                        </div>

                                        <!-- Card Body Snippet -->
                                        <div class="space-y-2 text-[11px] text-slate-600">
                                            @if(($event->event_type ?? 'normal') === 'inam_vitaran')
                                                <div>
                                                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider block">{{ __('messages.student_name') }}</span>
                                                    <span class="font-extrabold text-slate-900 text-xs block">{{ $fd['student_name'] ?? '-' }}</span>
                                                </div>
                                                <div class="grid grid-cols-2 gap-2">
                                                    <div>
                                                        <span class="text-[9px] font-bold text-slate-400 uppercase block">{{ __('messages.education') }}</span>
                                                        <span class="font-bold text-slate-800">{{ $fd['education'] ?? '-' }}</span>
                                                    </div>
                                                    <div>
                                                        <span class="text-[9px] font-bold text-slate-400 uppercase block">{{ __('messages.percentage') }}</span>
                                                        <span class="font-black text-amber-700 bg-amber-50 px-1.5 py-0.5 rounded border border-amber-200 inline-block text-[10px]">{{ $fd['percentage'] ?? '-' }}</span>
                                                    </div>
                                                </div>
                                            @elseif(($event->event_type ?? 'normal') === 'yuva_melo')
                                                <div>
                                                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider block">{{ __('messages.full_name') }}</span>
                                                    <span class="font-extrabold text-slate-900 text-xs block">{{ $fd['full_name'] ?? '-' }}</span>
                                                </div>
                                                <div class="grid grid-cols-2 gap-2">
                                                    <div>
                                                        <span class="text-[9px] font-bold text-slate-400 uppercase block">{{ __('messages.age_gender') }}</span>
                                                        <span class="font-bold text-slate-800">{{ $fd['age'] ?? '-' }} {{ __('messages.years') }} ({{ $fd['gender'] ?? '-' }})</span>
                                                    </div>
                                                    <div>
                                                        <span class="text-[9px] font-bold text-slate-400 uppercase block">{{ __('messages.mobile') }}</span>
                                                        <span class="font-bold text-slate-800">{{ $fd['mobile_no'] ?? '-' }}</span>
                                                    </div>
                                                </div>
                                            @else
                                                <div class="space-y-1">
                                                    <div>
                                                        <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider block">{{ __('messages.participant_name') }}</span>
                                                        <span class="font-extrabold text-slate-900 text-xs block">{{ $fd['full_name'] ?? '-' }}</span>
                                                    </div>
                                                    <div>
                                                        <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider block">{{ __('messages.person_count') }}</span>
                                                        <span class="font-bold text-primary-600 text-xs block">👥 {{ $fd['person_count'] ?? 1 }} {{ __('messages.persons') }}</span>
                                                    </div>
                                                </div>
                                            @endif

                                            @if(($event->pass_fee ?? 0) > 0 && ($event->event_type ?? 'normal') === 'normal')
                                                <div class="pt-1.5 border-t border-slate-100 flex items-center justify-between text-[10px]">
                                                    <span class="font-bold text-slate-500">💳 {{ __('messages.pass_fee') }}:</span>
                                                    <span class="font-extrabold px-1.5 py-0.5 rounded {{ ($reg->payment_status ?? 'unpaid') === 'paid' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-700' }}">
                                                        ₹{{ number_format($reg->payment_amount ?? $event->pass_fee, 0) }} ({{ strtoupper($reg->payment_status ?? 'unpaid') }})
                                                    </span>
                                                </div>
                                            @endif
                                        </div>

                                        <!-- Card Footer Action Actions (Details, Edit, Delete till last date) -->
                                        @php
                                            $isDeadlinePassed = !empty($event->form_end_date) && now()->toDateString() > \Carbon\Carbon::parse($event->form_end_date)->toDateString();
                                            $marksheetUrl = $fd['marksheet_url'] ?? null;
                                            if ($marksheetUrl && !str_starts_with($marksheetUrl, 'http')) {
                                                $marksheetUrl = asset('storage/' . $marksheetUrl);
                                            }
                                        @endphp
                                        <div class="pt-2 border-t border-slate-100 flex items-center justify-between gap-2 text-xs">
                                            @if($isYuva)
                                                <button type="button" @click.stop="selectedRegistration = {{ json_encode([
                                                    'id' => $reg->id,
                                                    'index' => $cardIndex,
                                                    'date' => $fd['submission_date'] ?? ($reg->created_at ? $reg->created_at->format('d-M-Y h:i A') : '-'),
                                                    'status' => $reg->status,
                                                    'form_data' => $fd
                                                ]) }}; showDetailsModal = true"
                                                    class="text-primary-600 hover:text-primary-700 font-extrabold flex items-center gap-1 cursor-pointer">
                                                    <span>🔍 {{ __('messages.details') }}</span>
                                                </button>
                                            @elseif(!empty($marksheetUrl))
                                                <a href="{{ $marksheetUrl }}" target="_blank"
                                                   class="px-2.5 py-1 bg-primary-50 hover:bg-primary-100 active:scale-95 text-primary-700 font-extrabold text-[11px] rounded-lg border border-primary-200/80 transition-all flex items-center gap-1 cursor-pointer shadow-2xs">
                                                    <span>📄 {{ __('messages.view_marksheet') }} ↗</span>
                                                </a>
                                            @else
                                                <span class="text-[10px] text-slate-400 font-medium italic">{{ __('messages.no_marksheet') }}</span>
                                            @endif

                                            @if(!$isDeadlinePassed)
                                                <div class="flex items-center gap-1.5 shrink-0" @click.stop>
                                                    <button type="button"
                                                        @click.stop="editRegistration({{ json_encode(['id' => $reg->id, 'form_data' => $fd]) }})"
                                                        class="px-2.5 py-1 bg-amber-50 hover:bg-amber-100 active:scale-95 text-amber-800 font-extrabold text-[11px] rounded-lg border border-amber-200/80 transition-all flex items-center gap-1 cursor-pointer shadow-2xs">
                                                        ✏️ {{ __('messages.edit') }}
                                                    </button>

                                                    <button type="button"
                                                        @click.stop="$dispatch('confirm-delete', { action: '{{ route('member.events.registrations.destroy', $reg->id) }}', message: '{{ __('messages.confirm_delete_registration') }}' })"
                                                        class="px-2 py-1 bg-rose-50 hover:bg-rose-100 active:scale-95 text-rose-700 font-extrabold text-[11px] rounded-lg border border-rose-200/80 transition-all flex items-center gap-1 cursor-pointer shadow-2xs">
                                                        🗑️ {{ __('messages.delete') }}
                                                    </button>
                                                </div>
                                            @else
                                                <span
                                                    class="text-[10px] font-bold text-slate-400 bg-slate-100 px-2 py-0.5 rounded border border-slate-200"
                                                    title="Editing period closed after last date">
                                                    🔒 {{ __('messages.closed') }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- FULL REGISTRATION DETAILS MODAL POPUP (BILINGUAL GUJARATI / ENGLISH BIODATA BOOKLET FORMAT) -->
            <template x-teleport="body">
                <div x-show="showDetailsModal"
                    class="fixed inset-0 z-50 flex items-center justify-center p-2 sm:p-4 bg-slate-900/75 backdrop-blur-sm"
                    x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-150"
                    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" x-cloak>
                    <div @click.away="showDetailsModal = false"
                        class="bg-white rounded-2xl border border-slate-300 shadow-2xl max-w-2xl w-full max-h-[92vh] flex flex-col overflow-hidden relative">

                        <!-- Modal Top Header Bar -->
                        <div class="px-4 py-2.5 bg-slate-900 text-white flex items-center justify-between shrink-0">
                            <div class="flex items-center gap-2">
                                <div>
                                    <h3 class="text-xs font-extrabold flex items-center gap-2">
                                        <span x-text="(('{{ $event->event_type ?? 'normal' }}' === 'yuva_melo' || selectedRegistration.form_data?.surname) ? (previewLang === 'en' ? 'Candidate Biodata Preview' : 'ઉમેદવાર બાયોડેટા પ્રીવ્યૂ (Candidate Biodata)') : 'Submitted Registration Details')"></span>
                                    </h3>
                                    <p class="text-[10px] text-slate-400 font-medium"
                                        x-text="(previewLang === 'en' ? 'Submitted on: ' : 'સબમિટ તારીખ: ') + (selectedRegistration.date || '')"></p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <!-- Language Toggle Switch (GU / EN) -->
                                <div class="inline-flex rounded-lg border border-slate-700 p-0.5 bg-slate-800 text-[11px] font-bold">
                                    <button type="button" @click="previewLang = 'gu'" 
                                        :class="previewLang === 'gu' ? 'bg-primary-600 text-white shadow-xs' : 'text-slate-300 hover:text-white'"
                                        class="px-2 py-0.5 rounded-md transition-colors cursor-pointer">
                                        ગુજરાતી
                                    </button>
                                    <button type="button" @click="previewLang = 'en'" 
                                        :class="previewLang === 'en' ? 'bg-primary-600 text-white shadow-xs' : 'text-slate-300 hover:text-white'"
                                        class="px-2 py-0.5 rounded-md transition-colors cursor-pointer">
                                        English
                                    </button>
                                </div>

                                <button type="button" @click="printBiodata()"
                                    class="px-2.5 py-1 bg-white/10 hover:bg-white/20 text-white rounded-lg text-xs font-bold transition-colors flex items-center gap-1 cursor-pointer">
                                    <span>🖨️ <span x-text="previewLang === 'en' ? 'Print' : 'પ્રિન્ટ'"></span></span>
                                </button>
                                <button type="button" @click="showDetailsModal = false"
                                    class="w-6 h-6 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition-colors text-xs cursor-pointer">
                                    ✕
                                </button>
                            </div>
                        </div>

                        <!-- Modal Body (Compact Scrollable with Traditional Gujarati Biodata Layout) -->
                        <div class="p-3 sm:p-4 overflow-y-auto text-xs bg-slate-100/70">

                            <!-- YUVA MELO TRADITIONAL BIODATA BOOKLET VIEW -->
                            <template x-if="'{{ $event->event_type ?? 'normal' }}' === 'yuva_melo' || selectedRegistration.form_data?.surname">
                                <div id="printableBiodata" class="bg-white p-3 border-2 border-slate-900 shadow-sm font-sans text-slate-900 space-y-2.5 max-w-[620px] mx-auto print:border-none print:p-0 print:shadow-none print:max-w-full">
                                    
                                    <!-- Top Box: Candidate Photo + Personal Header Summary -->
                                    <div class="border-2 border-slate-900 p-2.5 bg-white">
                                        <div class="flex gap-3 items-start">
                                            <!-- Left Photo Box (Fixed Passport Size) -->
                                            <div style="width: 110px; height: 140px; min-width: 110px; max-width: 110px; min-height: 140px; max-height: 140px; overflow: hidden; flex-shrink: 0;"
                                                class="shrink-0 border border-slate-900 rounded-xs bg-slate-50 relative flex items-center justify-center shadow-2xs">
                                                <template x-if="getPhotoUrl(selectedRegistration.form_data)">
                                                    <img :src="getPhotoUrl(selectedRegistration.form_data)"
                                                        style="width: 100%; height: 100%; object-fit: contain; display: block;"
                                                        class="w-full h-full object-contain bg-white">
                                                </template>
                                                <template x-if="!getPhotoUrl(selectedRegistration.form_data)">
                                                    <div class="text-center p-1 text-slate-400">
                                                        <svg class="w-8 h-8 mx-auto text-slate-300 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                                        <span class="text-[9px] font-bold" x-text="previewLang === 'en' ? 'No Photo' : 'ફોટો નથી'"></span>
                                                    </div>
                                                </template>
                                            </div>

                                            <!-- Right Personal & Contact Info -->
                                            <div class="flex-1 min-w-0 space-y-1 text-xs">
                                                <!-- Candidate Name (Red Bold) -->
                                                <h2 class="text-sm sm:text-base font-black text-rose-600 leading-tight"
                                                    x-text="((selectedRegistration.form_data?.first_name || '') + ' ' + (selectedRegistration.form_data?.surname || '')).trim() || selectedRegistration.form_data?.full_name || '-'">
                                                </h2>

                                                <!-- Father / Grandfather Full Name -->
                                                <div class="font-bold text-slate-800 text-[11px] leading-tight">
                                                    <span x-text="((selectedRegistration.form_data?.father_name || '') + ' ' + (selectedRegistration.form_data?.grandfather_name || '') + ' ' + (selectedRegistration.form_data?.surname || '')).trim() || '-'"></span>
                                                </div>

                                                <!-- Full Address -->
                                                <div class="text-[10.5px] text-slate-700 leading-tight">
                                                    <span x-text="[selectedRegistration.form_data?.address, selectedRegistration.form_data?.district, selectedRegistration.form_data?.state].filter(Boolean).join(', ') || '-'"></span>
                                                </div>

                                                <!-- Mobile Numbers -->
                                                <div class="text-[10.5px] font-bold flex flex-wrap gap-x-3 gap-y-0.5 pt-0.5">
                                                    <div>
                                                        <span class="text-slate-800" x-text="previewLang === 'en' ? 'Cand. Mob.:' : 'ઉ. મો.:'"></span>
                                                        <span class="text-blue-700 font-bold ml-0.5" x-text="selectedRegistration.form_data?.mobile_no || '-'"></span>
                                                    </div>
                                                    <div>
                                                        <span class="text-slate-800" x-text="previewLang === 'en' ? 'Guard. Mob.:' : 'વા. મો.:'"></span>
                                                        <span class="text-blue-700 font-bold ml-0.5" x-text="selectedRegistration.form_data?.father_mobile || selectedRegistration.form_data?.whatsapp || '-'"></span>
                                                    </div>
                                                </div>

                                                <!-- Mini Stats Table (DOB, Age, Height, Weight) -->
                                                <table class="w-full border-collapse border border-slate-900 text-[10px] text-center mt-1 table-fixed">
                                                    <colgroup>
                                                        <col style="width: 25%;">
                                                        <col style="width: 25%;">
                                                        <col style="width: 25%;">
                                                        <col style="width: 25%;">
                                                    </colgroup>
                                                    <tbody>
                                                        <tr class="border-b border-slate-900">
                                                            <td class="border-r border-slate-900 font-bold py-0.5 px-1 bg-slate-50 text-slate-800" x-text="previewLang === 'en' ? 'Birth Date' : 'જન્મ તારીખ'"></td>
                                                            <td class="border-r border-slate-900 font-bold py-0.5 px-1 text-blue-700 truncate" x-text="selectedRegistration.form_data?.birth_date || '-'"></td>
                                                            <td class="border-r border-slate-900 font-bold py-0.5 px-1 bg-slate-50 text-slate-800" x-text="previewLang === 'en' ? 'Age' : 'ઉંમર વર્ષ'"></td>
                                                            <td class="font-bold py-0.5 px-1 text-blue-700 truncate" x-text="(selectedRegistration.form_data?.age ? selectedRegistration.form_data?.age + (previewLang === 'en' ? ' Yrs' : ' વર્ષ') : '-')"></td>
                                                        </tr>
                                                        <tr>
                                                            <td class="border-r border-slate-900 font-bold py-0.5 px-1 bg-slate-50 text-slate-800" x-text="previewLang === 'en' ? 'Height' : 'ઊંચાઈ'"></td>
                                                            <td class="border-r border-slate-900 font-bold py-0.5 px-1 text-blue-700 truncate" x-text="selectedRegistration.form_data?.height || '-'"></td>
                                                            <td class="border-r border-slate-900 font-bold py-0.5 px-1 bg-slate-50 text-slate-800" x-text="previewLang === 'en' ? 'Weight' : 'વજન'"></td>
                                                            <td class="font-bold py-0.5 px-1 text-blue-700 truncate" x-text="selectedRegistration.form_data?.weight || '-'"></td>
                                                        </tr>
                                                    </tbody>
                                                </table>

                                                <!-- Native Place -->
                                                <div class="text-[10.5px] font-bold pt-0.5">
                                                    <span class="text-slate-800" x-text="previewLang === 'en' ? 'Native Place, District: ' : 'મૂળ વતન, ગામ, જિલ્લો: '"></span>
                                                    <span class="text-blue-700" x-text="selectedRegistration.form_data?.native_place ? selectedRegistration.form_data?.native_place + (selectedRegistration.form_data?.district ? ', ' + selectedRegistration.form_data?.district : '') : (selectedRegistration.form_data?.district || '-')"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Main Structured Table (Labels Left, Blue Values Right) -->
                                    <table class="w-full border-collapse border-2 border-slate-900 text-[10.5px] sm:text-[11px] text-left bg-white table-fixed">
                                        <colgroup>
                                            <col style="width: 34%;">
                                            <col style="width: 16%;">
                                            <col style="width: 34%;">
                                            <col style="width: 16%;">
                                        </colgroup>
                                        <tbody>
                                            <!-- Row 1: Qualification -->
                                            <tr class="border-b border-slate-900">
                                                <td class="border-r border-slate-900 py-1 px-2 font-bold text-slate-800 bg-slate-50/80" x-text="previewLang === 'en' ? 'Candidate Qualification' : 'ઉમેદવારની શૈક્ષણિક લાયકાત'"></td>
                                                <td colspan="3" class="py-1 px-2 font-bold text-blue-700 break-words" x-text="selectedRegistration.form_data?.qualification || '-'"></td>
                                            </tr>

                                            <!-- Row 2: Occupation -->
                                            <tr class="border-b border-slate-900">
                                                <td class="border-r border-slate-900 py-1 px-2 font-bold text-slate-800 bg-slate-50/80" x-text="previewLang === 'en' ? 'Candidate Occupation' : 'ઉમેદવારનો વ્યવસાય'"></td>
                                                <td colspan="3" class="py-1 px-2 font-bold text-blue-700 break-words" x-text="selectedRegistration.form_data?.occupation || '-'"></td>
                                            </tr>

                                            <!-- Row 3: Occupation Address -->
                                            <tr class="border-b border-slate-900">
                                                <td class="border-r border-slate-900 py-1 px-2 font-bold text-slate-800 bg-slate-50/80" x-text="previewLang === 'en' ? 'Occupation Address' : 'ઉમેદવારના વ્યવસાય નું સરનામું'"></td>
                                                <td colspan="3" class="py-1 px-2 font-bold text-blue-700 break-words" x-text="selectedRegistration.form_data?.occupation_address || '-'"></td>
                                            </tr>

                                            <!-- Row 4: Monthly Income -->
                                            <tr class="border-b border-slate-900">
                                                <td class="border-r border-slate-900 py-1 px-2 font-bold text-slate-800 bg-slate-50/80" x-text="previewLang === 'en' ? 'Monthly Income' : 'ઉમેદવારની માસિક આવક'"></td>
                                                <td colspan="3" class="py-1 px-2 font-bold text-blue-700 break-words" x-text="selectedRegistration.form_data?.monthly_income ? '₹ ' + selectedRegistration.form_data?.monthly_income : '-'"></td>
                                            </tr>

                                            <!-- Row 5: Sibling - Elder Brothers -->
                                            <tr class="border-b border-slate-900">
                                                <td class="border-r border-slate-900 py-1 px-2 font-bold text-slate-800 bg-slate-50/80" x-text="previewLang === 'en' ? 'Elder Brothers Count' : 'ઉમેદવારના મોટાભાઈની સંખ્યા'"></td>
                                                <td class="border-r border-slate-900 py-1 px-1.5 font-bold text-blue-700 text-center break-words" x-text="getSiblingStat(selectedRegistration.form_data, 'Elder Brother', false)"></td>
                                                <td class="border-r border-slate-900 py-1 px-2 font-bold text-slate-800 bg-slate-50/80 text-[10px]" x-text="previewLang === 'en' ? 'Married Elder Brothers' : 'પરણેલા મોટાભાઈની સંખ્યા'"></td>
                                                <td class="py-1 px-1.5 font-bold text-blue-700 text-center break-words" x-text="getSiblingStat(selectedRegistration.form_data, 'Elder Brother', true)"></td>
                                            </tr>

                                            <!-- Row 6: Sibling - Younger Brothers -->
                                            <tr class="border-b border-slate-900">
                                                <td class="border-r border-slate-900 py-1 px-2 font-bold text-slate-800 bg-slate-50/80" x-text="previewLang === 'en' ? 'Younger Brothers Count' : 'ઉમેદવારના નાનાભાઈની સંખ્યા'"></td>
                                                <td class="border-r border-slate-900 py-1 px-1.5 font-bold text-blue-700 text-center break-words" x-text="getSiblingStat(selectedRegistration.form_data, 'Younger Brother', false)"></td>
                                                <td class="border-r border-slate-900 py-1 px-2 font-bold text-slate-800 bg-slate-50/80 text-[10px]" x-text="previewLang === 'en' ? 'Married Younger Brothers' : 'પરણેલા નાનાભાઈની સંખ્યા'"></td>
                                                <td class="py-1 px-1.5 font-bold text-blue-700 text-center break-words" x-text="getSiblingStat(selectedRegistration.form_data, 'Younger Brother', true)"></td>
                                            </tr>

                                            <!-- Row 7: Sibling - Elder Sisters -->
                                            <tr class="border-b border-slate-900">
                                                <td class="border-r border-slate-900 py-1 px-2 font-bold text-slate-800 bg-slate-50/80" x-text="previewLang === 'en' ? 'Elder Sisters Count' : 'ઉમેદવારના મોટા બહેનો ની સંખ્યા'"></td>
                                                <td class="border-r border-slate-900 py-1 px-1.5 font-bold text-blue-700 text-center break-words" x-text="getSiblingStat(selectedRegistration.form_data, 'Elder Sister', false)"></td>
                                                <td class="border-r border-slate-900 py-1 px-2 font-bold text-slate-800 bg-slate-50/80 text-[10px]" x-text="previewLang === 'en' ? 'Married Elder Sisters' : 'પરણેલા મોટા બહેનો ની સંખ્યા'"></td>
                                                <td class="py-1 px-1.5 font-bold text-blue-700 text-center break-words" x-text="getSiblingStat(selectedRegistration.form_data, 'Elder Sister', true)"></td>
                                            </tr>

                                            <!-- Row 8: Sibling - Younger Sisters -->
                                            <tr class="border-b border-slate-900">
                                                <td class="border-r border-slate-900 py-1 px-2 font-bold text-slate-800 bg-slate-50/80" x-text="previewLang === 'en' ? 'Younger Sisters Count' : 'ઉમેદવારના નાના બહેનો ની સંખ્યા'"></td>
                                                <td class="border-r border-slate-900 py-1 px-1.5 font-bold text-blue-700 text-center break-words" x-text="getSiblingStat(selectedRegistration.form_data, 'Younger Sister', false)"></td>
                                                <td class="border-r border-slate-900 py-1 px-2 font-bold text-slate-800 bg-slate-50/80 text-[10px]" x-text="previewLang === 'en' ? 'Married Younger Sisters' : 'પરણેલા નાના બહેનો ની સંખ્યા'"></td>
                                                <td class="py-1 px-1.5 font-bold text-blue-700 text-center break-words" x-text="getSiblingStat(selectedRegistration.form_data, 'Younger Sister', true)"></td>
                                            </tr>

                                            <!-- Row 9: Father's Occupation -->
                                            <tr class="border-b border-slate-900">
                                                <td class="border-r border-slate-900 py-1 px-2 font-bold text-slate-800 bg-slate-50/80" x-text="previewLang === 'en' ? 'Father Occupation' : 'ઉમેદવારના પિતાનો વ્યવસાય'"></td>
                                                <td colspan="3" class="py-1 px-2 font-bold text-blue-700 break-words" x-text="selectedRegistration.form_data?.father_occupation || '-'"></td>
                                            </tr>

                                            <!-- Row 10: Father's Occupation Address -->
                                            <tr class="border-b border-slate-900">
                                                <td class="border-r border-slate-900 py-1 px-2 font-bold text-slate-800 bg-slate-50/80" x-text="previewLang === 'en' ? 'Father Occupation Address' : 'ઉમેદવારના પિતાના વ્યવસાયનું સરનામું'"></td>
                                                <td colspan="3" class="py-1 px-2 font-bold text-blue-700 break-words" x-text="selectedRegistration.form_data?.father_occupation_address || '-'"></td>
                                            </tr>

                                            <!-- Row 11: Mother's Name -->
                                            <tr class="border-b border-slate-900">
                                                <td class="border-r border-slate-900 py-1 px-2 font-bold text-slate-800 bg-slate-50/80" x-text="previewLang === 'en' ? 'Mother Name' : 'ઉમેદવારના માતાનું નામ'"></td>
                                                <td colspan="3" class="py-1 px-2 font-bold text-blue-700 break-words" x-text="selectedRegistration.form_data?.mother_name || '-'"></td>
                                            </tr>

                                            <!-- Row 12: Maternal Grandfather Address -->
                                            <tr class="border-b border-slate-900">
                                                <td class="border-r border-slate-900 py-1 px-2 font-bold text-slate-800 bg-slate-50/80" x-text="previewLang === 'en' ? 'Maternal Address' : 'ઉમેદવારના મોસાળ નું સરનામું'"></td>
                                                <td colspan="3" class="py-1 px-2 font-bold text-blue-700 break-words" x-text="selectedRegistration.form_data?.maternal_grandfather_address || '-'"></td>
                                            </tr>

                                            <!-- Row 13: Maternal Elder Name -->
                                            <tr class="border-b border-slate-900">
                                                <td class="border-r border-slate-900 py-1 px-2 font-bold text-slate-800 bg-slate-50/80" x-text="previewLang === 'en' ? 'Maternal Uncle / Grandfather' : 'મોસાળ ના વડીલનું નામ'"></td>
                                                <td colspan="3" class="py-1 px-2 font-bold text-blue-700 break-words" x-text="[selectedRegistration.form_data?.maternal_uncle_name, selectedRegistration.form_data?.maternal_grandfather_name].filter(Boolean).join(' / ') || '-'"></td>
                                            </tr>

                                            <!-- Row 14: Maternal Elder Occupation -->
                                            <tr class="border-b border-slate-900">
                                                <td class="border-r border-slate-900 py-1 px-2 font-bold text-slate-800 bg-slate-50/80" x-text="previewLang === 'en' ? 'Maternal Uncle / Grandfather Occupation' : 'મોસાળ ના વડીલ નો વ્યવસાય'"></td>
                                                <td colspan="3" class="py-1 px-2 font-bold text-blue-700 break-words" x-text="selectedRegistration.form_data?.maternal_grandfather_occupation || '-'"></td>
                                            </tr>

                                            <!-- Row 15: Physical Disability -->
                                            <tr class="border-b border-slate-900">
                                                <td class="border-r border-slate-900 py-1 px-2 font-bold text-slate-800 bg-slate-50/80" x-text="previewLang === 'en' ? 'Physical Disability' : 'ઉમેદવારની શારીરિક ખોડ-ખાંપણ'"></td>
                                                <td class="border-r border-slate-900 py-1 px-1.5 font-bold text-blue-700 text-center break-words" x-text="selectedRegistration.form_data?.physical_disability || (previewLang === 'en' ? 'None' : 'નથી')"></td>
                                                <td class="border-r border-slate-900 py-1 px-2 font-bold text-slate-800 bg-slate-50/80 text-[10px]" x-text="previewLang === 'en' ? 'Duration' : 'કેટલા સમયથી'"></td>
                                                <td class="py-1 px-1.5 font-bold text-blue-700 text-center break-words" x-text="selectedRegistration.form_data?.disability_duration || (previewLang === 'en' ? 'None' : 'નથી')"></td>
                                            </tr>

                                            <!-- Row 16: Divorce / Second Marriage -->
                                            <tr class="border-b border-slate-900">
                                                <td class="border-r border-slate-900 py-1 px-2 font-bold text-slate-800 bg-slate-50/80" x-text="previewLang === 'en' ? 'Divorce / Other Details' : 'છૂટા-છેડા, બીજા લગ્ન અન્ય માહિતી'"></td>
                                                <td colspan="3" class="py-1 px-2 font-bold text-blue-700 break-words" x-text="(selectedRegistration.form_data?.divorce === 'Yes' ? (previewLang === 'en' ? 'Yes (Divorced)' : 'હા (Yes)') : (previewLang === 'en' ? 'None' : 'નથી')) + (selectedRegistration.form_data?.other_info ? ' - ' + selectedRegistration.form_data?.other_info : '')"></td>
                                            </tr>

                                            <!-- Row 17: Special Info -->
                                            <tr>
                                                <td class="border-r border-slate-900 py-1 px-2 font-bold text-slate-800 bg-slate-50/80" x-text="previewLang === 'en' ? 'Special Information' : 'વિશેષ માહિતી'"></td>
                                                <td colspan="3" class="py-1 px-2 font-bold text-blue-700 break-words" x-text="selectedRegistration.form_data?.special_info || '-'"></td>
                                            </tr>
                                        </tbody>
                                    </table>

                                  
                                </div>
                            </template>

                            <!-- STANDARD / INAM VITARAN EVENT DETAILS VIEW -->
                            <template x-if="'{{ $event->event_type ?? 'normal' }}' !== 'yuva_melo' && !selectedRegistration.form_data?.surname">
                                <div class="space-y-2.5">
                                    <!-- Uploaded Documents & Photos Compact Strip -->
                                    <template x-if="Object.keys(selectedRegistration.form_data || {}).some(k => k.endsWith('_url'))">
                                        <div class="space-y-1">
                                            <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-wider">Uploaded Documents & Photos</h4>
                                            <div class="flex flex-wrap items-center gap-2 bg-slate-50 p-2 rounded-xl border border-slate-100">
                                                <template x-for="(val, key) in (selectedRegistration.form_data || {})" :key="key">
                                                    <template x-if="key.endsWith('_url') && val">
                                                        <div class="flex items-center gap-2 bg-white px-2 py-1 rounded-lg border border-slate-200 shadow-2xs">
                                                            <a :href="val" target="_blank"
                                                                class="block w-8 h-8 shrink-0 overflow-hidden rounded bg-slate-100 border border-slate-200">
                                                                <img :src="val" class="w-full h-full object-cover">
                                                            </a>
                                                            <div class="min-w-0">
                                                                <span class="text-[9px] font-bold text-slate-700 uppercase block truncate max-w-[100px]"
                                                                    x-text="key.replace('_url', '').replace(/_/g, ' ')"></span>
                                                                <a :href="val" target="_blank"
                                                                    class="text-[9px] font-bold text-primary-600 hover:underline">View File ↗</a>
                                                            </div>
                                                        </div>
                                                    </template>
                                                </template>
                                            </div>
                                        </div>
                                    </template>

                                    <!-- Complete Form Data Grid -->
                                    <div class="space-y-1">
                                        <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-wider">Submitted Form Fields</h4>
                                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-1.5">
                                            <template x-for="(val, key) in (selectedRegistration.form_data || {})" :key="key">
                                                <template x-if="!key.endsWith('_url') && key !== 'submission_date'">
                                                    <div class="bg-white px-2 py-1 rounded-lg border border-slate-200 hover:bg-slate-50 transition-colors shadow-2xs">
                                                        <span class="text-[8px] font-black text-slate-400 uppercase tracking-wider block truncate"
                                                            x-text="key.replace(/_/g, ' ')"></span>
                                                        <span class="font-bold text-slate-900 text-[10.5px] block break-words leading-tight mt-0.5"
                                                            x-text="val || '-'"></span>
                                                    </div>
                                                </template>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </template>

                        </div>

                        <!-- Modal Footer -->
                        <div class="px-4 py-2.5 bg-slate-50 border-t border-slate-200 flex items-center justify-end shrink-0">
                            <button type="button" @click="showDetailsModal = false"
                                class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-xl transition-colors cursor-pointer">
                                Close Details
                            </button>
                        </div>
                    </div>
                </div>
            </template>

            <!-- SIBLING ADD POPUP MODAL -->
            <template x-teleport="body">
                <div x-show="showSiblingModal"
                    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
                    x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" x-cloak>

                    <div class="bg-white rounded-2xl border border-slate-100 shadow-2xl w-full max-w-md overflow-hidden"
                        @click.away="showSiblingModal = false">

                        <!-- Modal Header -->
                        <div class="px-5 py-4 bg-slate-900 text-white flex items-center justify-between">
                            <h3 class="font-extrabold text-xs uppercase tracking-wider">
                                + Add Sibling Details
                            </h3>
                            <button type="button" @click="showSiblingModal = false"
                                class="text-slate-400 hover:text-white font-bold text-lg leading-none">
                                &times;
                            </button>
                        </div>

                        <!-- Modal Body -->
                        <div class="p-5 space-y-4 text-xs">
                            <!-- Relation Dropdown -->
                            <div class="space-y-1">
                                <label class="font-bold text-slate-700 block text-[11px]">
                                    Relation <span class="text-rose-500">*</span>
                                </label>
                                <select x-model="newSibling.relation"
                                    class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:bg-white focus:border-primary-500 outline-none">
                                    <option value="Elder Brother">Elder Brother</option>
                                    <option value="Younger Brother">Younger Brother</option>
                                    <option value="Elder Sister">Elder Sister</option>
                                    <option value="Younger Sister">Younger Sister</option>
                                </select>
                            </div>

                            <!-- Name / Details -->
                            <div class="space-y-1">
                                <label class="font-bold text-slate-700 block text-[11px]">
                                    Name / Count / Details
                                </label>
                                <input type="text" x-model="newSibling.details" placeholder="e.g. Ramesh Bhai / 1 Brother"
                                    class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:bg-white focus:border-primary-500 outline-none">
                            </div>

                            <!-- Marital Status -->
                            <div class="space-y-1">
                                <label class="font-bold text-slate-700 block text-[11px]">
                                    Marital Status <span class="text-rose-500">*</span>
                                </label>
                                <select x-model="newSibling.married"
                                    class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:bg-white focus:border-primary-500 outline-none">
                                    <option value="No">Unmarried</option>
                                    <option value="Yes">Married</option>
                                </select>
                            </div>

                            <!-- Occupation / Notes -->
                            <div class="space-y-1">
                                <label class="font-bold text-slate-700 block text-[11px]">
                                    Occupation / Notes (Optional)
                                </label>
                                <input type="text" x-model="newSibling.occupation" placeholder="e.g. Job in IT / Student"
                                    class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:bg-white focus:border-primary-500 outline-none">
                            </div>
                        </div>

                        <!-- Modal Footer -->
                        <div class="px-5 py-3.5 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-2">
                            <button type="button" @click="showSiblingModal = false"
                                class="px-4 py-2 bg-slate-200 hover:bg-slate-300 font-bold text-slate-700 rounded-xl transition-colors text-xs cursor-pointer">
                                Cancel
                            </button>
                            <button type="button" @click="addSibling()"
                                class="px-4 py-2 bg-primary-600 hover:bg-primary-700 font-bold text-white rounded-xl shadow-xs transition-colors text-xs cursor-pointer">
                                + Add Sibling
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        </div>
        @if(!request()->routeIs('member.*') || !auth()->check())
            </div>
        @endif

@if(($event->event_type ?? 'normal') === 'yuva_melo')
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('eventDynamicRegisterForm');
    if (!form) return;

    function showFormError(message, tabNumber, inputElement) {
        const banner = document.getElementById('yuvaFormErrorBanner');
        const msgSpan = document.getElementById('yuvaFormErrorMessage');
        if (banner && msgSpan) {
            msgSpan.textContent = message;
            banner.style.display = 'flex';
            banner.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        if (tabNumber) {
            const alpineEl = form.closest('[x-data]');
            if (alpineEl && window.Alpine) {
                Alpine.$data(alpineEl).yuvaTab = tabNumber;
            }
        }

        if (inputElement) {
            setTimeout(function () {
                inputElement.focus();
                inputElement.classList.add('ring-2', 'ring-rose-500', 'border-rose-500');
                setTimeout(function () {
                    inputElement.classList.remove('ring-2', 'ring-rose-500', 'border-rose-500');
                }, 4000);
            }, 200);
        }
    }

    form.addEventListener('submit', function (e) {
        const paymentIdInput = document.getElementById('dynamic_razorpay_payment_id');
        if (paymentIdInput && paymentIdInput.value) {
            return true; // Already paid, let submit proceed
        }

        // 1. Validate Step 1 (Candidate Info)
        const surnameInput = form.querySelector('[name="surname"]');
        const firstNameInput = form.querySelector('[name="first_name"]');
        const genderInput = form.querySelector('[name="gender"]');
        const birthDateInput = form.querySelector('[name="birth_date"]');
        const ageInput = form.querySelector('[name="age"]');
        const addressInput = form.querySelector('[name="address"]');
        const mobileInput = form.querySelector('[name="mobile_no"]');
        const qualInput = form.querySelector('[name="qualification"]');
        const occInput = form.querySelector('[name="occupation"]');

        if (!surnameInput?.value.trim()) {
            e.preventDefault();
            showFormError("Step 1: Please enter candidate's Surname.", 1, surnameInput);
            return false;
        }
        if (!firstNameInput?.value.trim()) {
            e.preventDefault();
            showFormError("Step 1: Please enter candidate's First Name.", 1, firstNameInput);
            return false;
        }
        if (!genderInput?.value) {
            e.preventDefault();
            showFormError("Step 1: Please select Gender.", 1, genderInput);
            return false;
        }
        if (!birthDateInput?.value) {
            e.preventDefault();
            showFormError("Step 1: Please select Birth Date.", 1, birthDateInput);
            return false;
        }
        if (!ageInput?.value || parseInt(ageInput.value) <= 0) {
            e.preventDefault();
            showFormError("Step 1: Please enter valid Age.", 1, ageInput);
            return false;
        }
        if (!addressInput?.value.trim()) {
            e.preventDefault();
            showFormError("Step 1: Please enter candidate's Address.", 1, addressInput);
            return false;
        }
        if (!mobileInput?.value.trim() || mobileInput.value.trim().length < 10) {
            e.preventDefault();
            showFormError("Step 1: Please enter 10-digit Mobile Number.", 1, mobileInput);
            return false;
        }
        if (!qualInput?.value.trim()) {
            e.preventDefault();
            showFormError("Step 1: Please enter Qualification.", 1, qualInput);
            return false;
        }
        if (!occInput?.value.trim()) {
            e.preventDefault();
            showFormError("Step 1: Please enter Occupation.", 1, occInput);
            return false;
        }

        // 2. Validate Step 2 (Father & Family Info)
        const fatherInput = form.querySelector('[name="father_name"]');
        const grandFatherInput = form.querySelector('[name="grandfather_name"]');
        const motherInput = form.querySelector('[name="mother_name"]');
        const nativeInput = form.querySelector('[name="native_place"]');

        if (!fatherInput?.value.trim()) {
            e.preventDefault();
            showFormError("Step 2: Please enter Father's Name.", 2, fatherInput);
            return false;
        }
        if (!grandFatherInput?.value.trim()) {
            e.preventDefault();
            showFormError("Step 2: Please enter Grandfather's Name.", 2, grandFatherInput);
            return false;
        }
        if (!motherInput?.value.trim()) {
            e.preventDefault();
            showFormError("Step 2: Please enter Mother's Name.", 2, motherInput);
            return false;
        }
        if (!nativeInput?.value.trim()) {
            e.preventDefault();
            showFormError("Step 2: Please enter Native Place.", 2, nativeInput);
            return false;
        }

        // 3. Validate Step 3 (Maternal Info)
        const maternalUncleInput = form.querySelector('[name="maternal_uncle_name"]');
        const maternalGrandfatherInput = form.querySelector('[name="maternal_grandfather_name"]');

        if (!maternalUncleInput?.value.trim()) {
            e.preventDefault();
            showFormError("Step 3: Please enter Maternal Uncle's Name.", 3, maternalUncleInput);
            return false;
        }
        if (!maternalGrandfatherInput?.value.trim()) {
            e.preventDefault();
            showFormError("Step 3: Please enter Maternal Grandfather's Name.", 3, maternalGrandfatherInput);
            return false;
        }

        // Hide any previous error banner
        const banner = document.getElementById('yuvaFormErrorBanner');
        if (banner) banner.style.display = 'none';

        const formFee = {{ (float)($event->form_fee ?? 0) }};
        if (formFee <= 0) {
            // Free form - allow direct submission
            return true;
        }

        e.preventDefault();

        const totalAmountPaise = Math.round(formFee * 100);
        const razorpayKey = "{{ \App\Models\Setting::get('razorpay_key_id', env('RAZORPAY_KEY_ID', '')) }}";
        
        const candidateName = ((surnameInput?.value || '') + ' ' + (firstNameInput?.value || '')).trim() || "{{ auth()->user() ? auth()->user()->name : '' }}";
        const candidatePhone = mobileInput?.value || "{{ (auth()->user() && auth()->user()->memberProfile) ? auth()->user()->memberProfile->phone : '' }}";
        const candidateEmail = form.querySelector('[name="email"]')?.value || "{{ auth()->user() ? auth()->user()->email : '' }}";

        const options = {
            "key": razorpayKey || "rzp_test_key",
            "amount": totalAmountPaise,
            "currency": "INR",
            "name": "{{ config('app.name', 'Shree Satwara Gnati Mandal, Ahmedabad') }}",
            "description": "Yuva Melo Registration Form Fee - {{ addslashes($event->title) }}",
            "handler": function (response) {
                if (paymentIdInput) {
                    paymentIdInput.value = response.razorpay_payment_id;
                }
                window.dispatchEvent(new CustomEvent('close-all-modals'));
                form.submit();
            },
            "prefill": {
                "name": candidateName,
                "email": candidateEmail,
                "contact": candidatePhone
            },
            "theme": {
                "color": "#7C3AED"
            }
        };

        if (window.Razorpay) {
            const rzp = new Razorpay(options);
            rzp.on('payment.failed', function (response) {
                showFormError("Payment Failed: " + (response.error.description || "Could not complete transaction."), null, null);
            });
            rzp.open();
        } else {
            showFormError("Razorpay Payment Gateway could not be loaded. Please refresh and try again.", null, null);
        }
    });
});
</script>
@endif
@endsection