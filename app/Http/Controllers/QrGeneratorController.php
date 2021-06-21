<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use PDF;
use Faker;

class QrGeneratorController extends Controller
{
    private $prefixes = [
        'WEBSITE_URL' => 'Website URL (http://)',
        'E-MAIL' => 'E-mail Address (mailto:)',
        'PHONE_NUMBER' => 'Phone Number (tel:)',
        'SMS' => 'Text (SMS) (sms:)',
        'MECARD' => 'MeCard (mecard:)',
    ];

    private $red;
    private $green;
    private $blue;
    private $faker;

    public function __construct(Request $request)
    {
        $this->faker = Faker\Factory::create();
        
        list($this->red, $this->green, $this->blue) = sscanf($request['color'], "#%02x%02x%02x");
    }

    public function index()
    {
        $prefixes = $this->prefixes;
        return view('welcome', compact('prefixes'));
    }

    public function generate(Request $request)
    {
        $this->validateRequest($request);
        
        $qrCodes = $this->getQrCodes($request);

        return $this->printPdf($qrCodes);
    }

    private function validateRequest($request)
    {
        $prefixes = implode(',', array_keys($this->prefixes));
        
        $request->validate([
            'code-count' => 'required|integer|min:1|max:100',
            'prefix' => "required|in:$prefixes",
            'color' => 'required',
        ]);
    }

    private function getQrCodes($request)
    {
        switch ($request['prefix'])
        {
            case 'WEBSITE_URL':
                return $this->getWebsiteCode($request['code-count']);
            break;
            case 'E-MAIL':
                return $this->getEmailCode($request['code-count']);
            break;
            case 'PHONE_NUMBER':
                return $this->getPhoneNumberCode($request['code-count']);
            break;
            case 'SMS':
                return $this->getSmsCode($request['code-count']);
            break;
            case 'MECARD':
                return $this->getMeCardCode($request['code-count']);
            break;
        }
    }

    private function getWebsiteCode($codesCount)
    {
        $codes = [];
        
        for ($i = 0; $i < $codesCount; $i++) {
            $website = $this->faker->unique()->domainName;

             $code = QrCode::format('svg')
                ->color($this->red, $this->green, $this->blue)
                ->generate($website);
                
            $codes[] = [ 
                'data' => $website,
                'code' => $this->repaireSvgCode($code)
            ];
        }
        return $codes;
    }

    private function getEmailCode($codesCount)
    {
        $codes = [];
        
        for ($i = 0; $i < $codesCount; $i++) {
            $email = $this->faker->unique()->email;

             $code = QrCode::format('svg')
                ->color($this->red, $this->green, $this->blue)
                ->generate($email);
                
            $codes[] = [ 
                'data' => $email,
                'code' => $this->repaireSvgCode($code)
            ];
        }
        return $codes;
    }

    private function getPhoneNumberCode($codesCount)
    {
        $codes = [];
        
        for ($i = 0; $i < $codesCount; $i++) {
            $phoneNumber = $this->faker->unique()->phoneNumber;

            $code = QrCode::color($this->red, $this->green, $this->blue)
                ->phoneNumber($phoneNumber);
                
            $codes[] = [ 
                'data' => $phoneNumber,
                'code' => $this->repaireSvgCode($code)
            ];
        }
        return $codes;
    }

    private function getSmsCode($codesCount)
    {
        $codes = [];
        
        for ($i = 0; $i < $codesCount; $i++) {
            $sms = $this->faker->unique()->realText($maxNbChars = 100, $indexSize = 2);

             $code = QrCode::format('svg')
                ->color($this->red, $this->green, $this->blue)
                ->generate($sms);
                
            $codes[] = [ 
                'data' => $sms,
                'code' => $this->repaireSvgCode($code)
            ];
        }
        return $codes;
    }

    private function getMeCardCode($codesCount)
    {
        $codes = [];
        
        for ($i = 0; $i < $codesCount; $i++) {
            $sms = $this->faker->unique()->iban();

             $code = QrCode::format('svg')
                ->color($this->red, $this->green, $this->blue)
                ->generate($sms);
                
            $codes[] = [ 
                'data' => $sms,
                'code' => $this->repaireSvgCode($code)
            ];
        }
        return $codes;
    }

    private function repaireSvgCode($code)
    {
        return str_replace('<?xml version="1.0" encoding="UTF-8"?>', '', $code);
    }

    private function printPdf($codes)
    {
        $html = $this->generateHtml($codes);
        $pdf = PDF::loadHTML($html);
        return $pdf->download('Dynamic QR Codes.pdf');
    }

    private function generateHtml($codes)
    {
        $html = '';
        for($i = 0; $i < count($codes); $i++) {
            $html .= '<img src="data:image/svg+xml;base64,' . base64_encode($codes[$i]['code']) 
            . '"  width="100" height="100" />'
            . '<h5 style="margin-bottom: .5rem;">' . $codes[$i]['data'] .'</h5>';
        }
        return $html;
    }

}
