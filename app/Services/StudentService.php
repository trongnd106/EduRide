<?php

namespace App\Services;

use App\Models\Student;
use Illuminate\Support\Str;

class StudentService extends  BaseService
{
    protected QRCodeService $qrCodeService;

    public function __construct(QRCodeService $qrCodeService = null)
    {
        parent::__construct();
        $this->qrCodeService = $qrCodeService ?? new QRCodeService();
    }

    public function model()
    {
        return Student::class;
    }

    /**
     * Store a newly created student with auto-generated QR code.
     *
     * @param array $attributes
     * @return \Illuminate\Database\Eloquent\Model|bool
     * @throws \Exception
     */
    public function store(array $attributes)
    {
        $student = parent::store($attributes);
        
        if (!$student) {
            return false;
        }

        if (!isset($attributes['qr_code_image_url']) || empty($attributes['qr_code_image_url'])) {
            $qrCodeValue = "STUDENT_{$student->id}";
            
            $qrCodeImageUrl = $this->qrCodeService->generateAndSave(
                $qrCodeValue,
                "student_{$student->id}"
            );
            
            if ($qrCodeImageUrl) {
                $student->qr_code_image_url = $qrCodeImageUrl;
                $student->save();
            }
        }

        return $student;
    }

    /**
     * Update student and regenerate QR code image if needed.
     *
     * @param int $id
     * @param array $attributes
     * @return \Illuminate\Database\Eloquent\Model|bool
     * @throws \Exception
     */
    public function update(int $id, array $attributes)
    {
        $student = $this->show($id);
        
        if (!isset($attributes['qr_code_image_url']) || empty($attributes['qr_code_image_url'])) {
            if ($student->qr_code_image_url) {
                $this->qrCodeService->delete($student->qr_code_image_url);
            }
            
            $qrCodeValue = "STUDENT_{$student->id}";
            $qrCodeImageUrl = $this->qrCodeService->generateAndSave(
                $qrCodeValue,
                "student_{$student->id}"
            );
            
            if ($qrCodeImageUrl) {
                $attributes['qr_code_image_url'] = $qrCodeImageUrl;
            }
        }

        return parent::update($id, $attributes);
    }
}
