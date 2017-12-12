<?php

class EditableAddressFinderField extends EditableFormField
{
    private static $singular_name = 'Address Finder Field';

    private static $plural_name = 'Address Finder Fields';

    private static $has_many = array(
        "Mappings" => "EditableAddressFinderMapping"
    );

    public function getCMSFields()
    {

        $fields = parent::getCMSFields();

        $editableColumns = new GridFieldEditableColumns();
        $editableColumns->setDisplayFields(array(
            'EditableFieldID' => array(
                'title' => 'Userform Field ID',
                'callback' => function ($record, $column, $grid) {
                    return TextField::create($column);
                }
            ),
            'NZPostFieldID' => array(
                'title' => 'NZ Post Field ID',
                'callback' => function ($record, $column, $grid) {
                    return TextField::create($column);
                }
            )
        ));

        $optionsConfig = GridFieldConfig::create()
            ->addComponents(
                new GridFieldToolbarHeader(),
                new GridFieldTitleHeader(),
                new GridFieldOrderableRows('Sort'),
                $editableColumns,
                new GridFieldButtonRow(),
                new GridFieldAddNewInlineButton(),
                new GridFieldDeleteAction()
            );

        $optionsGrid = GridField::create(
            'Mappings',
            'Field Mappings',
            $this->Mappings(),
            $optionsConfig
        );

        $fields->addFieldsToTab(
            'Root.Mappings',
            [
                $optionsGrid,
                LiteralField::create(
                    'NZPostFields',
                    $this->getNZPostFieldMap()
                )
            ]
        );
        return $fields;
    }

    public function getNZPostFieldMap() {
        $html = "<div><h4>The following field values are available for capture from NZ Post</h4>";
        $html .= '<code>
            "DPID": 3111226,<br>
            "AddressLine1": "7 Waterloo Quay",<br>
            "AddressLine2": "Pipitea",<br>
            "AddressLine3": "Wellington 6011",<br>
            "AddressLine4": null,<br>
            "AddressLine5": null,<br>
            "Postcode": "6011",<br>
            "SourceDesc": "Postal\\Physical - Not Delivered",<br>
            "Deliverable": "N",<br>
            "Physical": "Y",<br>
            "UnitType": null,<br>
            "UnitValue": null,<br>
            "Floor": null,<br>
            "StreetNumber": 7,<br>
            "StreetAlpha": null,<br>
            "RoadName": "Waterloo",<br>
            "RoadTypeName": "Quay",<br>
            "RoadSuffixName": null,<br>
            "Suburb": "Pipitea",<br>
            "RuralDelivery": null,<br>
            "Lobby": null,<br>
            "CityTown": "Wellington",<br>
            "MailTown": null,<br>
            "BoxBagNumber": null,<br>
            "BoxBagType": null<br>
        </code>';

        $html .= "</div>";
        return $html;
    }

    public function getFieldMappings() {
        if ($this->Mappings()) {
            $arr = [];
            foreach ($this->Mappings() as $map) {
                $arr[$map->EditableFieldID] = $map->NZPostFieldID;
            }

            return $arr;
        }

        return false;
    }

    public function getSetsOwnError()
    {
        return true;
    }

    public function getFormField()
    {
        $field = AddressFinderField::create($this->Name, $this->EscapedTitle, $this->Default)
            ->setFieldHolderTemplate('UserFormsField_holder');

        if (!empty($this->getFieldMappings())) {
            $field->setFieldMap($this->getFieldMappings());
        }

        return $field;
    }
}
