<?php

use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\Assets\Image;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextareaField;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\DropdownField;
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
        // Get dropdown options
        $provinceOptions = $this->getProvinceOptions();
        $cityOptions = $this->getCityOptions();
        $districtOptions = $this->getDistrictOptions();

        $fields->addFieldsToTab('Root.Main', [
            TextField::create('Email', 'Email Company'),
            TextField::create('Phone', 'Phone Company'),
            TextField::create('Address', 'Address Company'),

            DropdownField::create('CompanyProvinceID', 'Provinsi Perusahaan', $provinceOptions)
                ->setEmptyString('Pilih Provinsi'),
            DropdownField::create('CompanyCityID', 'Kota/Kabupaten Perusahaan', $cityOptions)
                ->setEmptyString('Pilih Kota/Kabupaten'),
            DropdownField::create('CompanyDistricID', 'Kecamatan Perusahaan', $districtOptions)
                ->setEmptyString('Pilih Kecamatan'),

            TextField::create('CompanyPostalCode', 'Kode Pos'),
            TextField::create('Credit', 'Credit Company'),
            UploadField::create('favicon', 'favicon'),
            UploadField::create('logo', 'Logo'),

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

        // Add JavaScript for cascading dropdowns
        $fields->addFieldToTab('Root.Main', 
            \SilverStripe\Forms\LiteralField::create('AddressScript', $this->getAddressScript())
        );
    }

    private function getProvinceOptions()
    {
        try {
            $rajaOngkir = new RajaOngkirService();
            $provinces = $rajaOngkir->getProvinces();
            
            $options = [];
            foreach ($provinces as $province) {
                $options[$province['id']] = $province['name'];
            }
            
            return $options;
        } catch (Exception $e) {
            return [];
        }
    }

    private function getCityOptions()
    {
        if (!$this->owner->CompanyProvinceID) {
            return [];
        }

        try {
            $rajaOngkir = new RajaOngkirService();
            $cities = $rajaOngkir->getCities($this->owner->CompanyProvinceID);
            
            $options = [];
            foreach ($cities as $city) {
                $options[$city['id']] = $city['name'];
            }
            
            return $options;
        } catch (Exception $e) {
            return [];
        }
    }

    private function getDistrictOptions()
    {
        if (!$this->owner->CompanyCityID) {
            return [];
        }

        try {
            $rajaOngkir = new RajaOngkirService();
            $districts = $rajaOngkir->getDistricts($this->owner->CompanyCityID);
            
            $options = [];
            foreach ($districts as $district) {
                $options[$district['id']] = $district['name'];
            }
            
            return $options;
        } catch (Exception $e) {
            return [];
        }
    }

    private function getAddressScript()
    {
        return '
        <script>
        (function() {
            function updateCascadingDropdowns() {
                var provinceSelect = document.querySelector("#Form_EditForm_CompanyProvinceID");
                var citySelect = document.querySelector("#Form_EditForm_CompanyCityID");
                var districtSelect = document.querySelector("#Form_EditForm_CompanyDistricID");
                
                if (!provinceSelect || !citySelect || !districtSelect) return;
                
                provinceSelect.addEventListener("change", function() {
                    var provinceId = this.value;
                    
                    // Clear dependent dropdowns
                    citySelect.innerHTML = "<option value=\\"\\">Pilih Kota/Kabupaten</option>";
                    districtSelect.innerHTML = "<option value=\\"\\">Pilih Kecamatan</option>";
                    
                    if (provinceId) {
                        // Load cities via AJAX
                        fetch("/checkout/api/cities/" + provinceId)
                            .then(response => response.json())
                            .then(data => {
                                data.forEach(function(city) {
                                    var option = new Option(city.name, city.id);
                                    citySelect.add(option);
                                });
                            })
                            .catch(error => console.error("Error loading cities:", error));
                    }
                });
                
                citySelect.addEventListener("change", function() {
                    var cityId = this.value;
                    
                    // Clear district dropdown
                    districtSelect.innerHTML = "<option value=\\"\\">Pilih Kecamatan</option>";
                    
                    if (cityId) {
                        // Load districts via AJAX
                        fetch("/checkout/api/districts/" + cityId)
                            .then(response => response.json())
                            .then(data => {
                                data.forEach(function(district) {
                                    var option = new Option(district.name, district.id);
                                    districtSelect.add(option);
                                });
                            })
                            .catch(error => console.error("Error loading districts:", error));
                    }
                });
            }
            
            // Initialize when DOM is ready
            if (document.readyState === "loading") {
                document.addEventListener("DOMContentLoaded", updateCascadingDropdowns);
            } else {
                updateCascadingDropdowns();
            }
        })();
        </script>';
    }
}