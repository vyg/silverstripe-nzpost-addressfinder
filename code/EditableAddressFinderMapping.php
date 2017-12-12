<?php

class EditableAddressFinderMapping extends DataObject
{
    private static $db = [
        'EditableFieldID' => 'Varchar',
        'NZPostFieldID' => 'Varchar',
        'Sort' => 'Int'
    ];

    private static $has_one = [
        'Parent' => 'EditableAddressFinderField'
    ];
}
