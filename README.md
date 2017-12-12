# NZ Post Address Finder

Address finder module for NZ Post API.

> This is currently using the 1.0-client version of the API. All requests require your client_id and client_secret and does not use the Oauth implementation as in 1.0.

## Getting started

Sign up with NZ Post for API access.
Grab your client_id and client_secret and add to your `config.yml` file

```yml
NZPOSTAPI:
  client_id: "1234567890"
  client_secret: "1234567890"
```

This module comes with both the `AddressFinderField` for use in your own Forms as well as the `EditableAddressFinderField` for use in Userforms.

### Form Field

Add the AddressFinderField in your Form like below. Make sure you add `setFieldMap` and include the fields you want to map the returned results to.
The key should always be your form field ID and the value will be the NZ Post field (these values can be found below or in the NZ Post API documentation).

```php
$field = AddressFinderField::create('AddressFinderField', 'Search for an address')
  ->setFieldMap([
    "FormField_AddressLine1" => "AddressLine1",
    "FormField_AddressLine2" => "AddressLine2",
    "FormField_City" => "CityTown",
    "FormField_Postcode" => "Postcode"
  ]);
```

### Userforms Field

This module comes with a field for use in Userforms. The `EditableAddressFinderField` adds a mapping tab to the edit view of the field. Where you can add multiple mappings.

Each mapping requires the user forms field ID or name as well as a corresponding NZ Post value to grab.


#### Content Editors

When using the Userforms Field, content editors need to manually select the fields they want mapped via the CMS.

On the Field editing view, there is a tab for Mappings. From here a content editor can add a new field mapping and match another Userform Field ID/Name to an NZ Post Field ID.

The name for a userform field can be found in the Fields main tab.


#### Field Mappings

The following fields are available from NZ Post for mapping. The values shown are just examples based on the returned response.

```json
"DPID": 3111226,
"AddressLine1": "7 Waterloo Quay",
"AddressLine2": "Pipitea",
"AddressLine3": "Wellington 6011",
"AddressLine4": null,
"AddressLine5": null,
"Postcode": "6011",
"SourceDesc": "Postal\\Physical - Not Delivered",
"Deliverable": "N",
"Physical": "Y",
"UnitType": null,
"UnitValue": null,
"Floor": null,
"StreetNumber": 7,
"StreetAlpha": null,
"RoadName": "Waterloo",
"RoadTypeName": "Quay",
"RoadSuffixName": null,
"Suburb": "Pipitea",
"RuralDelivery": null,
"Lobby": null,
"CityTown": "Wellington",
"MailTown": null,
"BoxBagNumber": null,
"BoxBagType": null
```

## Contributing

1. Fork it!
2. Create your feature branch: `git checkout -b my-new-feature`
3. Commit your changes: `git commit -am 'Add some feature'`
4. Push to the branch: `git push origin my-new-feature`
5. Submit a pull request :D


## Acknowledgements

[Silverstripe Google Address Field](https://github.com/sunnysideup/silverstripe-google_address_field) - Inspiration for mapping field values

## License

## TODO
- [ ] Add unit tests
- [ ] Add JS testing
- [ ] Make SS4 compatible
- [ ] Finish setting up Oauth
