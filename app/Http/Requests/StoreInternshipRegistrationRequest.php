<?php

namespace App\Http\Requests;

use App\InternshipRegistration;
use App\StudentProfile;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInternshipRegistrationRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $mobile = ['required', 'regex:/^[6-9][0-9]{9}$/'];

        return [
            'name'                => ['required', 'string', 'max:255'],
            'email'               => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'mobile'              => $mobile,
            'whatsapp'            => $mobile,
            'guardian_name'       => ['required', 'string', 'max:255'],
            'gender'              => ['required', Rule::in(array_keys(StudentProfile::GENDER_SELECT))],
            'password'            => ['required', 'string', 'min:8', 'max:255'],
            'payment_method'      => ['required', Rule::in([InternshipRegistration::PAYMENT_METHOD_ONLINE, InternshipRegistration::PAYMENT_METHOD_OFFLINE])],
            'internship_id'       => ['required', 'integer', Rule::exists('internships', 'id')->whereNull('deleted_at')->where('is_active', 1)->where('college_id', $this->input('college_id'))],

            // Every level must exist AND belong to the level above it, so a
            // tampered request cannot save a mismatched chain.
            'university_id'       => ['required', 'integer', Rule::exists('universities', 'id')->whereNull('deleted_at')],
            'college_id'          => ['required', 'integer', Rule::exists('colleges', 'id')->whereNull('deleted_at')->where('university_id', $this->input('university_id'))],
            'stream_id'           => ['required', 'integer', Rule::exists('streams', 'id')->whereNull('deleted_at')->where('college_id', $this->input('college_id'))],
            'subject_id'          => ['required', 'integer', Rule::exists('subjects', 'id')->whereNull('deleted_at')->where('stream_id', $this->input('stream_id'))],

            'class_semester'      => ['required', Rule::in(array_keys(StudentProfile::classSemesterOptions()))],
            'year_of_study'       => ['required', Rule::in(array_keys(StudentProfile::YEAR_OF_STUDY_SELECT))],
            'registration_number' => ['required', 'string', 'max:100'],
            'roll_number'         => ['required', 'string', 'max:100'],
            'academic_session'    => ['required', Rule::in(array_keys(StudentProfile::academicSessionOptions()))],
        ];
    }

    public function messages()
    {
        return [
            'mobile.regex'        => 'Enter a valid 10-digit Indian mobile number.',
            'whatsapp.regex'      => 'Enter a valid 10-digit Indian WhatsApp number.',
            'college_id.exists'   => 'The selected college does not belong to the selected university.',
            'stream_id.exists'    => 'The selected stream does not belong to the selected college.',
            'subject_id.exists'   => 'The selected subject does not belong to the selected stream.',
            'internship_id.exists' => 'The selected programme is not available for your college.',
            'payment_method.in'   => 'Invalid payment method selected.',
        ];
    }

    public function attributes()
    {
        return [
            'guardian_name'       => 'parent / guardian name',
            'internship_id'       => 'programme',
            'university_id'       => 'university',
            'college_id'          => 'college',
            'stream_id'           => 'stream',
            'subject_id'          => 'major subject',
            'class_semester'      => 'class/semester',
            'year_of_study'       => 'year of study',
            'registration_number' => 'registration number',
            'roll_number'         => 'roll number',
            'academic_session'    => 'academic session',
            'payment_method'      => 'payment method',
        ];
    }
}
