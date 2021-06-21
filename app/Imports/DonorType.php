<?php

namespace App\Imports;

use App\Models\DonorType as DonorTypeModel;
use App\Models\User;
use Exception;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class DonorType implements ToCollection
{
    /**
     * @param Collection $contacts
     */
    public function collection(Collection $contacts)
    {
        foreach ($contacts as $key => $row) {

            try {
                if (($row[1] || $row[2]) && ($key > 0)) {

                    $user = User::query();

                    $dataInsert = [];

                    if ($row[1]) {
                        $dataInsert['phone_number'] = $row[1];
                        $user = $user->orWhere('phone_number', $row[1]);
                    }

                    if ($row[2]) {
                        $dataInsert['email'] = $row[2];
                        $user = $user->orWhere('email', $row[2]);
                    }

                    if ($row[0]) {
                        $dataInsert['full_name'] = $row[0];
                    }

                    if ($row[3]) {
                        $dataInsert['registered_at'] = $row[3];
                    }

                    $user = $user->first();

                    if (!$user) {

                        $dataInsert['member_id'] = env('APP_MEMBER');

                        $user = User::create($dataInsert);

                    } else {


                        if ($row[0]) {
                            $user->update([
                                'full_name' => $row[0]
                            ]);
                        }

                        if (!$user->registered_at) {

                            $user->update([
                                'registered_at' => $row[3]
                            ]);

                        } elseif (strtotime($user->registered_at) > strtotime($row[3])) {
                            $user->update([
                                'registered_at' => $row[3]
                            ]);
                        }

                    }


                    echo "---------------------------------------------\n";
                    echo "$user\n";
                    echo "$row\n";
                    echo "---------------------------------------------\n";


                    if ($row[4]) {
                        DonorTypeModel::updateOrCreate(
                            [
                                'donor_id' => $user->id,
                                'expired_at' => '2018-12-31'
                            ],
                            [
                                'type' => ucwords($row[4])
                            ]
                        );
                    }

                    if ($row[5]) {
                        DonorTypeModel::updateOrCreate(
                            [
                                'donor_id' => $user->id,
                                'expired_at' => '2019-12-31'
                            ],
                            [
                                'type' => ucwords($row[5])
                            ]
                        );
                    }

                    if ($row[6]) {
                        DonorTypeModel::updateOrCreate(
                            [
                                'donor_id' => $user->id,
                                'expired_at' => '2020-12-31'
                            ],
                            [
                                'type' => ucwords($row[6])
                            ]
                        );
                    }

                }

                echo "row " . $key . "/" . count($contacts) . "\n";
            } catch (Exception $exception) {
                echo $exception->getMessage();
            }

        }

        echo 'beres ' . date('Y-m-d H:i:s');

    }
}
