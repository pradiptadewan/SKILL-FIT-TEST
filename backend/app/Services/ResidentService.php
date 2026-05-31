<?php

namespace App\Services;

use App\Models\Resident;
use App\Repositories\Contracts\ResidentRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ResidentService
{
    public function __construct(private ResidentRepositoryInterface $residents)
    {
    }

    public function create(array $data, UploadedFile $ktpPhoto): Resident
    {
        $data['ktp_photo_path'] = $ktpPhoto->store('ktp-photos', 'public');

        try {
            return $this->residents->create($data);
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($data['ktp_photo_path']);

            throw $exception;
        }
    }

    public function update(Resident $resident, array $data, ?UploadedFile $ktpPhoto): Resident
    {
        if ($ktpPhoto) {
            $oldPath = $resident->ktp_photo_path;
            $newPath = $ktpPhoto->store('ktp-photos', 'public');
            $data['ktp_photo_path'] = $newPath;

            try {
                $resident = $this->residents->update($resident, $data);
            } catch (Throwable $exception) {
                Storage::disk('public')->delete($newPath);

                throw $exception;
            }

            Storage::disk('public')->delete($oldPath);

            return $resident;
        }

        return $this->residents->update($resident, $data);
    }

    public function delete(Resident $resident): void
    {
        $this->residents->delete($resident);
    }
}
