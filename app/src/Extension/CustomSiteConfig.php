<?php

use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\Assets\Image;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextareaField;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\LiteralField;
use SilverStripe\ORM\DataExtension;

class CustomSiteConfig extends DataExtension
{
    private static $table_name = 'CompanyConfig';
    private static $db = [
        "Email" => "Varchar(255)",
        "Phone" => "Varchar(20)",
        "Address" => "Text",
        "CompanyProvinceID" => "Int",
        "CompanyCityID" => "Int",
        "CompanyDistricID" => "Int",
        "CompanyPostalCode" => "Int",
        "Credit" => "Varchar(255)",
        "AboutTitle" => "Varchar(255)",
        "AboutDescription" => "Text",
        "SubAbout1Title" => "Varchar(255)",
        "SubAbout1Description" => "Text",
        "SubAbout2Title" => "Varchar(255)",
        "SubAbout2Description" => "Text",
        "SubAbout3Title" => "Varchar(255)",
        "SubAbout3Description" => "Text",
        "SubAbout4Title" => "Varchar(255)",
        "SubAbout4Description" => "Text",
    ];
    private static $has_one = [
        "favicon" => Image::class,
        "logo" => Image::class,
    ];
    private static $owns = [
        "favicon",
        "logo",
    ];

    public function updateCMSFields(FieldList $fields)
    {
        $fields->addFieldsToTab('Root.Main', [
            TextField::create('Email', 'Email Company'),
            TextField::create('Phone', 'Phone Company'),
            TextField::create('Address', 'Address Company'),

            // Company Address Section with current data display
            LiteralField::create('CompanyAddressHeader', '<h3>Alamat Perusahaan (untuk Perhitungan Ongkir)</h3>'),

            TextField::create('CompanyProvinceID', 'ID Provinsi'),
            LiteralField::create('ProvinceInfo', $this->getProvinceInfo()),

            TextField::create('CompanyCityID', 'ID Kota/Kabupaten'),
            LiteralField::create('CityInfo', $this->getCityInfo()),

            TextField::create('CompanyDistricID', 'ID Kecamatan'),
            LiteralField::create('DistrictInfo', $this->getDistrictInfo()),

            TextField::create('CompanyPostalCode', 'Kode Pos'),

            LiteralField::create('AddressNote', '<div class="alert alert-info">
                <strong>Catatan:</strong> Gunakan ID dari API Raja Ongkir. ID Kecamatan akan digunakan sebagai alamat asal pengiriman.
                <br><a href="https://rajaongkir.com" target="_blank">Cek ID di dokumentasi Raja Ongkir</a>
            </div>'),

            TextField::create('Credit', 'Credit Company'),
            UploadField::create('favicon', 'favicon'),
            UploadField::create('logo', 'Logo'),

            // About Section
            LiteralField::create('AboutHeader', '<h3>Tentang Perusahaan</h3>'),
            TextField::create('AboutTitle', 'Title About'),
            TextareaField::create('AboutDescription', 'Description About'),
            TextField::create('SubAbout1Title', 'Sub Title About 1'),
            TextareaField::create('SubAbout1Description', 'Sub Description About 1'),
            TextField::create('SubAbout2Title', 'Sub Title About 2'),
            TextareaField::create('SubAbout2Description', 'Sub Description About 2'),
            TextField::create('SubAbout3Title', 'Sub Title About 3'),
            TextareaField::create('SubAbout3Description', 'Sub Description About 3'),
            TextField::create('SubAbout4Title', 'Sub Title About 4'),
            TextareaField::create('SubAbout4Description', 'Sub Description About 4'),
        ]);
    }

    private function getProvinceInfo()
    {
        if (!$this->owner->CompanyProvinceID) {
            return '<div class="alert alert-warning">
                <strong>Belum ada provinsi yang dipilih</strong>
                <br><small>Masukkan ID Provinsi dari API Raja Ongkir</small>
            </div>';
        }

        $provinceName = $this->getProvinceName($this->owner->CompanyProvinceID);

        if ($provinceName) {
            return '<div class="alert alert-success">
                <strong>Provinsi:</strong> ' . $provinceName . '
                <br><small>ID: ' . $this->owner->CompanyProvinceID . '</small>
            </div>';
        } else {
            return '<div class="alert alert-danger">
                <strong>ID Provinsi tidak valid:</strong> ' . $this->owner->CompanyProvinceID . '
                <br><small>Periksa kembali ID Provinsi dari API Raja Ongkir</small>
            </div>';
        }
    }

    private function getCityInfo()
    {
        if (!$this->owner->CompanyCityID) {
            return '<div class="alert alert-warning">
                <strong>Belum ada kota yang dipilih</strong>
                <br><small>Masukkan ID Kota/Kabupaten dari API Raja Ongkir</small>
            </div>';
        }

        if (!$this->owner->CompanyProvinceID) {
            return '<div class="alert alert-warning">
                <strong>Pilih provinsi terlebih dahulu</strong>
                <br><small>ID Provinsi diperlukan untuk validasi kota</small>
            </div>';
        }

        $cityName = $this->getCityName($this->owner->CompanyCityID, $this->owner->CompanyProvinceID);

        if ($cityName) {
            return '<div class="alert alert-success">
                <strong>Kota/Kabupaten:</strong> ' . $cityName . '
                <br><small>ID: ' . $this->owner->CompanyCityID . '</small>
            </div>';
        } else {
            return '<div class="alert alert-danger">
                <strong>ID Kota tidak valid:</strong> ' . $this->owner->CompanyCityID . '
                <br><small>Periksa kembali ID Kota/Kabupaten dari API Raja Ongkir</small>
            </div>';
        }
    }

    private function getDistrictInfo()
    {
        if (!$this->owner->CompanyDistricID) {
            return '<div class="alert alert-warning">
                <strong>Belum ada kecamatan yang dipilih</strong>
                <br><small>Masukkan ID Kecamatan dari API Raja Ongkir</small>
            </div>';
        }

        if (!$this->owner->CompanyCityID) {
            return '<div class="alert alert-warning">
                <strong>Pilih kota terlebih dahulu</strong>
                <br><small>ID Kota diperlukan untuk validasi kecamatan</small>
            </div>';
        }

        $districtName = $this->getDistrictName($this->owner->CompanyDistricID, $this->owner->CompanyCityID);

        if ($districtName) {
            return '<div class="alert alert-success">
                <strong>Kecamatan:</strong> ' . $districtName . '
                <br><small>ID: ' . $this->owner->CompanyDistricID . '</small>
                <br><em>ID ini akan digunakan sebagai alamat asal pengiriman</em>
            </div>';
        } else {
            return '<div class="alert alert-danger">
                <strong>ID Kecamatan tidak valid:</strong> ' . $this->owner->CompanyDistricID . '
                <br><small>Periksa kembali ID Kecamatan dari API Raja Ongkir</small>
            </div>';
        }
    }

    private function getProvinceName($provinceId)
    {
        try {
            $rajaOngkir = new RajaOngkirService();
            $provinces = $rajaOngkir->getProvinces();

            foreach ($provinces as $province) {
                if ($province['id'] == $provinceId) {
                    return $province['name'];
                }
            }
        } catch (Exception $e) {
            // API call failed, return null
            return null;
        }

        return null;
    }

    private function getCityName($cityId, $provinceId)
    {
        try {
            $rajaOngkir = new RajaOngkirService();
            $cities = $rajaOngkir->getCities($provinceId);

            foreach ($cities as $city) {
                if ($city['id'] == $cityId) {
                    return $city['name'];
                }
            }
        } catch (Exception $e) {
            // API call failed, return null
            return null;
        }

        return null;
    }

    private function getDistrictName($districtId, $cityId)
    {
        try {
            $rajaOngkir = new RajaOngkirService();
            $districts = $rajaOngkir->getDistricts($cityId);

            foreach ($districts as $district) {
                if ($district['id'] == $districtId) {
                    return $district['name'];
                }
            }
        } catch (Exception $e) {
            // API call failed, return null
            return null;
        }

        return null;
    }
}