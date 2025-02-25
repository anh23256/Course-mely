<?php

namespace App\Imports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;;

use Illuminate\Support\Str;
use App\Traits\LoggableTrait;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithSkipDuplicates;
use Spatie\Permission\Models\Role;

class UsersImport implements ToModel, WithHeadingRow, WithBatchInserts, WithChunkReading, SkipsEmptyRows, WithMapping, WithSkipDuplicates, ShouldQueue
{
    use LoggableTrait;

    const URLIMAGEDEFAULT = "https://res.cloudinary.com/dvrexlsgx/image/upload/v1732148083/Avatar-trang-den_apceuv_pgbce6.png";
    private $role;
    private $emails = [];

    public function __construct($role = 'member')
    {
        $validRoles = Role::query()->pluck('name')->toArray();

        $this->role = !in_array($role, $validRoles) ? 'member' : $role;
    }

    public function model(array $row)
    {
        $this->emails[] = $row['email'] ?? null;

        return new User($row);
    }

    public function map($row): array
    {
        return [
            'email' => strtolower(trim($row['email'])),
            'name' => Str::ucfirst($row['name']),
            'code' => substr(str_replace('-', '', Str::uuid()), 0, 10),
            'avatar' => self::URLIMAGEDEFAULT,
            'email_verified_at' => now(env('APP_TIMEZONE')),
            'password' => bcrypt('Coursemely'),
            'created_at' => now(env('APP_TIMEZONE')),
            'updated_at' => now(env('APP_TIMEZONE')),
        ];
    }

    public function chunkSize(): int
    {
        return 8000 * 10;
    }

    public function batchSize(): int
    {
        return 1000;
    }
    public function __destruct()
    {
        if (!empty($this->emails)) {
            $users = User::whereIn('email', $this->emails)->get();
            foreach ($users as $user) {
                $user->syncRoles($this->role);
            }
        }
    }
}
