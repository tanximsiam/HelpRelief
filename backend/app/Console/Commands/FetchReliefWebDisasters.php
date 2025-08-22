<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use App\Models\DisasterAlert;

class FetchReliefWebDisasters extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fetch-reliefweb-disasters';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch latest disasters from ReliefWeb Disasters API';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
        $divisionMap = [
            'Dhaka' => 'Dhaka', 'Faridpur' => 'Dhaka', 'Gazipur' => 'Dhaka', 'Gopalganj' => 'Dhaka',
            'Kishoreganj' => 'Dhaka', 'Madaripur' => 'Dhaka', 'Manikganj' => 'Dhaka', 'Munshiganj' => 'Dhaka',
            'Narayanganj' => 'Dhaka', 'Narsingdi' => 'Dhaka', 'Rajbari' => 'Dhaka', 'Shariatpur' => 'Dhaka',
            'Tangail' => 'Dhaka',


            'Jamalpur' => 'Mymensingh', 'Mymensingh' => 'Mymensingh', 'Netrokona' => 'Mymensingh', 'Sherpur' => 'Mymensingh',


            'Bogra' => 'Rajshahi', 'Joypurhat' => 'Rajshahi', 'Naogaon' => 'Rajshahi', 'Natore' => 'Rajshahi',
            'Chapainawabganj' => 'Rajshahi', 'Pabna' => 'Rajshahi', 'Rajshahi' => 'Rajshahi', 'Sirajganj' => 'Rajshahi',


            'Dinajpur' => 'Rangpur', 'Gaibandha' => 'Rangpur', 'Kurigram' => 'Rangpur', 'Lalmonirhat' => 'Rangpur',
            'Nilphamari' => 'Rangpur', 'Panchagarh' => 'Rangpur', 'Rangpur' => 'Rangpur', 'Thakurgaon' => 'Rangpur',


            'Barguna' => 'Barisal', 'Barisal' => 'Barisal', 'Bhola' => 'Barisal', 'Jhalokati' => 'Barisal',
            'Patuakhali' => 'Barisal', 'Pirojpur' => 'Barisal',


            'Bandarban' => 'Chittagong', 'Brahmanbaria' => 'Chittagong', 'Chandpur' => 'Chittagong', 'Chittagong' => 'Chittagong',
            'Comilla' => 'Chittagong', 'Cox’s Bazar' => 'Chittagong', 'Feni' => 'Chittagong', 'Khagrachhari' => 'Chittagong',
            'Lakshmipur' => 'Chittagong', 'Noakhali' => 'Chittagong', 'Rangamati' => 'Chittagong',


            'Habiganj' => 'Sylhet', 'Maulvibazar' => 'Sylhet', 'Sunamganj' => 'Sylhet', 'Sylhet' => 'Sylhet',


            'Bagerhat' => 'Khulna', 'Chuadanga' => 'Khulna', 'Jessore' => 'Khulna', 'Jhenaidah' => 'Khulna',
            'Khulna' => 'Khulna', 'Kushtia' => 'Khulna', 'Magura' => 'Khulna', 'Meherpur' => 'Khulna',
            'Narail' => 'Khulna', 'Satkhira' => 'Khulna',
        ];


        $districts = array_keys($divisionMap);
        $disasterMap = [];

        $from = Carbon::now()->subDays(90)->toIso8601String();


        $response = Http::get('https://api.reliefweb.int/v1/disasters', [
            'appname' => 'helprelief',
            'profile' => 'full',
            'limit'   => 50,
            'sort'    => ['date.created:desc'],
            'query'   => ['value' => 'Bangladesh'],
            'filter'  => [
                'conditions' => [
                    ['field' => 'date.created', 'value' => ['from' => $from]],
                    // keep country-wide scope (query) and do district parsing from description as you do now
                ]
            ]
        ]);


        if (!$response->ok()) {
        $this->error("Failed to fetch from ReliefWeb.");
        return;
        }


        foreach ($response->json()['data'] ?? [] as $item) {
            $fields = $item['fields'];
            $title = $fields['name'] ?? 'N/A';
            $type = $fields['primary_type']['name'] ?? 'Unknown';
            $status = ucfirst($fields['status'] ?? 'Unknown');
            if ($status === 'Past') continue;
            $desc = $fields['description'] ?? '';
            $created = $fields['date']['created'] ?? 'Unknown';


            foreach ($districts as $district) {
                if (str_contains($desc, $district)) {
                    $division = $divisionMap[$district];
                    $disasterMap[$title]['meta'] = [$type, $status, $created];
                    $disasterMap[$title]['divisions'][$division] = true;
                }
            }
        }


        foreach ($disasterMap as $title => $info) {
            [$type, $status, $created] = $info['meta'];
            $divisions = implode(', ', array_keys($info['divisions']));

            DisasterAlert::updateOrCreate(
                ['title' => $title, 'reported_at' => $created],
                [
                    'disaster_type' => $type,
                    'status' => $status,
                    'description' => $fields['url'] ?? '',
                    'divisions' => json_encode($divisions),
                    'confirmed' => 'pending',
                ]
            );


            $this->line("• [$type][$status] $title → Divisions: $divisions → Time: $created");
        }


        $this->info("\n✅ Grouped by unique disasters and affected divisions.");
    }
}
