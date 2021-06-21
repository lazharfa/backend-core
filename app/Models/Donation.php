<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Payment;

class Donation extends Model
{
    protected $fillable = [
        'member_id',
        'campaign_id',
        'type_donation',
        'date_donation',
        'donor_id',
        'created_id',
        'donation',
        'unique_value',
        'total_donation',
        'bank_id',
        'donor_type',
        'donor_name',
        'donor_phone',
        'donor_email',
        'verified_id',
        'verified_at',
        'auto_verified_at',
        'staff_id',
        'expired_at',
        'anonymous',
        'note',
        'prayer',
        'channel_source',
        'channel_medium',
        'channel_campaign',
        'channel_term',
        'channel_content',
        'donation_number',
        'ovo_phone',
        'ovo_status',
        'message_sent_at',
        'email_sent_at',
        'whatsapp_sent_at',
        'reminder_at',
        'push_to_balans_at'
    ];

    protected $dates = [
        'date_donation',
        'expired_at'
    ];

    protected $casts = [
        'donation' => 'double',
        'unique_value' => 'double',
        'total_donation' => 'double',
        'anonymous' => 'boolean',
    ];

    protected $appends = ['status', 'is_extra'];

    public function getStatusAttribute()
    {
        if ($this->total_donation > 0) {
            return 'paid';
        }

        if (strtotime(now()) > strtotime($this->expired_at)) {
            return 'expired';
        }

        return 'pending';
    }

    public function getIsExtraAttribute()
    {
        $member = env('APP_MEMBER');
        return $this->donor_email == "extradonor@$member" ? true : false;
    }

    public static function getUniqueValue($value)
    {
        $historyUnique = DB::table('history_unique')->get()->map(function ($item, $key) {

            return $item->total_donation;

        })->values()->all();

        $arrayRange = range($value + 1, $value + 10000);


        $resultArray = array_values(array_diff($arrayRange, $historyUnique));

        return $resultArray[0] - $value;

    }

    public function donor()
    {
        return $this->belongsTo(User::class, 'donor_id', 'id');
    }

    public function scopeOfMember($query, $member)
    {
        return $query->where('member_id', $member);
    }

    public function scopeExtraDonationFilter($query, $extra)
    {
        switch ($extra) {
            case 'true':
                return $query->whereHas('donor', function($q){
                    $member = env('APP_MEMBER');
                    $q->where('email', "extradonor@$member");
                });
                break;
            
            case 'false':
                return $query->whereDoesntHave('donor', function($q){
                    $member = env('APP_MEMBER');
                    $q->where('email', "extradonor@$member");
                });
                break;
            
            default: break;
        }
    }

    public function scopeOfficerFilter($query, $officer)
    {
        if ($officer) {
            $query->where('channel_term', 'ilike', "%$officer%");
        }
    }

    public function scopeBankIdFilter($query, $bank_id)
    {
        if ($bank_id) {
            $query->where('bank_id', $bank_id);
        }
    }

    public function scopeStatusFilter($query, $status)
    {
        switch ($status) {
            case 'paid':
                $query->whereNotNull('total_donation');
                break;
            
            case 'pending':
                $query->whereNull('total_donation')->where('expired_at', '>=', date('Y-m-d H:i:s'));
                break;

            case 'expired':
                $query->whereNull('total_donation')->where('expired_at', '<', date('Y-m-d H:i:s'));
                break;
            
            default: break;
        }
    }

    public function scopeSearch($query, $search)
    {
        if ($search) {
            $query->where(function($q) use($search) {
                $q->where('donor_name', 'ilike', "%$search%")
                ->orWhere('donation_number', 'ilike', "%$search%")
                ->orWhere('donor_email', 'ilike', "%$search%")
                ->orWhere('donor_phone', 'ilike', "%$search%")
                ->orWhereRaw('(donation + unique_value)::text like ?', [strtolower('%' . $search . '%')])
                ->orWhereHas('campaign', function($q1) use($search) {
                    $q1->where('campaign_title', 'ilike', "%$search%");
                });
            });
        }
    }

    public function scopeSort($query, $sort)
    {
        switch ($sort) {
            case 'time_asc':
                $query->orderBy('date_donation');
                break;
            
            case 'time_desc':
                $query->orderBy('date_donation', 'desc');
                break;
            
            case 'status_asc':
                $query->orderBy('total_donation', 'desc');
                break;

            case 'status_desc':
                $query->orderBy('total_donation');
                break;
            
            default: break;
        }
    }

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function bank()
    {
        return $this->belongsTo(MemberBank::class, 'bank_id', 'id');
    }

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    public function qurban_order()
    {
        return $this->hasMany(QurbanOrder::class);
    }

    public function payment()
    {
        return $this->hasMany(Payment::class);
    }

    public static function paidDonation($donation, $total_payment, $bank_id, $transaction_time, $payment_type)
    {
        if ($donation->total_donation == null) {
            Payment::updateOrCreate(
                [
                    'member_id' => env('APP_MEMBER'),
                    'total_payment' => $total_payment,
                    'bank_id' => $bank_id,
                    'payment_at' => Carbon::parse($transaction_time)->subHour(7)
                ],
                [
                    'description' => 'pay with ' . $payment_type,
                    'donation_id' => $donation->id,
                    'claim_at' => now()
                ]
            );

            $donation->update([
                'unique_value' => 0,
                'auto_verified_at' => now(),
                'total_donation' => $total_payment
            ]);
        }
    }

    public static function guideDonation($donation)
    {
        if (env('GUIDE_DONOR', false)) {

            $campaignTitle = $donation->campaign_id ? $donation->campaign->campaign_title : $donation->type_donation;
            $totalDonation = number_format($donation->donation + $donation->unique_value, 0, '', '.');;
            $bank = $donation->bank;
            $paymentMethod = $bank->bank_info . " an. " . $bank->bank_account . " " . $bank->bank_number;
            $expiredAt = $donation->expired_at->addHours(7)->format('d M Y H.i');

            $qurbanOrders = QurbanOrder::where('donation_id', $donation->id)->get();

            $qurbanNames = null;
            $qurbanType = null;
            $qurbanOrder = null;
            $fileName = null;
            $qurbanTypeCount = null;

            if ($qurbanOrders->count() > 0) {

                $qurbanOrder = $qurbanOrders->first();
                $qurbanType = $qurbanOrder->qurban_type;
                $qurbanTypeCount = $qurbanOrders->filter(function($qurbanOrder) {
                    return $qurbanOrder->parent_id == null;
                })->count();

                if ($qurbanOrders->count() > 1) {
                    $qurbanNames = $qurbanOrders->pluck('qurban_name');
                } else {
                    $qurbanNames = [ $qurbanOrder->qurban_name ];
                }

            }

            if ($donation->donor_phone) {

                if (env('SEND_WHATSAPP', false)) {
                    WhatsappJob::firstOrCreate(
                        [
                            'job_type' => 'Guide',
                            'donation_id' => $donation->id,
                            'whatsapp_number' => $donation->donor_phone,
                            'worker' => env('WHATSAPP_WORKER', 'info-ibm')
                        ],
                        [
                            'job_status' => 'On Queue',
                            'priority' => 1,
                            'whatsapp_name' => $donation->donor_name,
                            'worker_mode' => 'anon',
                        ]
                    );
                }

                switch (env('APP_MEMBER')) {
                    case 'insanbumimandiri.org':
                        if ($donation->type_donation == 'Qurban ' . date('Y')) {
                            $messageText = "Terimakasih {$donation->donor_name}. Mohon transfer {$totalDonation} ke {$paymentMethod} untuk program kurban di pedalaman sebelum {$expiredAt}";
                        } else {
                            $messageText = "Terimakasih {$donation->donor_name}. Mohon transfer {$totalDonation} ke {$paymentMethod} untuk program {$campaignTitle} sebelum {$expiredAt}";
                        }
                        break;

                    case 'rumahasuh.org':
                        $paymentMethod = "$bank->bank_info $bank->bank_number an. $bank->bank_account";
                        $messageText = "Terima kasih Bapak/Ibu {$donation->donor_name}, atas niat baiknya untuk berbagi sesama melalui Rumah Asuh. Selangkah lagi untuk mengiringi perjuangan mereka. Kirim donasi sahabat melalui rekening {$paymentMethod} untuk program {$campaignTitle}, sebelum {$expiredAt} WIB";
                        break;

                    case 'pesantrenquran.org':
                        $expiredAt = $donation->expired_at->addHours(7)->format('d M Y H.i');
                        $messageText = "Terimakasih Bapak/Ibu {$donation->donor_name}, mohon transfer TEPAT {$totalDonation}, ke rek {$donation->bank->bank_info} an {$donation->bank->bank_account} {$donation->bank->bank_number} untuk program {$campaignTitle} sebelum {$expiredAt}, Jazaakumullah khair. ";
                        break;
                }

                Message::zenzivaV1($messageText, $donation->donor_phone);
            }

            if ($donation->donor_email) {

                $memberName = explode( ".", env('APP_MEMBER'))[0];

                if ($donation->type_donation == 'Qurban ' . date('Y')) {
                    $data = array(
                        "donation" => $donation,
                        "qurbanNames" => $qurbanNames,
                        "qurbanType" => $qurbanType,
                        "qurbanOrder" => $qurbanOrder,
                        "qurbanTypeCount" => $qurbanTypeCount
                    );

                    $fileName = "emails.$memberName.guide-qurban";
                    $subject = "Konfirmasi Pembayaran Kurban";
                } else {
                    $data = array(
                        "donation" => $donation
                    );

                    $fileName = "emails.$memberName.guide-donation";
                    $subject = "Donate Confirmations";
                }

                Mail::send($fileName, $data, function ($message) use ($donation, $subject) {
                    $message->to($donation->donor_email, $donation->donor_name)
                        ->subject($subject);
                    $message->from(env('MAIL_SENDER'), env('EMAIL_NAME'));
                });

            }

        }
    }
}
