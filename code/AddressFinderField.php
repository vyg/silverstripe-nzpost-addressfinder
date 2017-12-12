<?php

class AddressFinderField extends TextField
{
    private $client;

    private static $allowed_actions = [
        'suggestions',
        'details'
    ];

    /**
     * List of fields to map our returned result to
     * @var array
     */
    protected $fieldMap = array();

    /**
     * @param array $array
     */
    public function setFieldMap($array)
    {
        $this->fieldMap = $array;
        return $this;
    }

    public function getFieldMap()
    {
        return $this->fieldMap;
    }


    /**
     * Returns an input field.
     *
     * @param string $name
     * @param null|string $title
     * @param string $value
     * @param null|int $maxLength
     * @param null|Form $form
     */
    public function __construct($name, $title = null, $value = '')
    {
        $this->client = new AddressFinderService;
        parent::__construct($name, $title, $value);
    }

    /**
     * @return string
     */
    public function Field($properties = array())
    {
        if (Director::isDev()) {
            Requirements::javascript(ADDRESSFINDER_PATH . '/dist/javascript/nzpostautocomplete.js');
        } else {
            Requirements::javascript(ADDRESSFINDER_PATH . '/dist/javascript/nzpostautocomplete.min.js');
        }

        $this->addExtraClass('nzpost-autocomplete text');
        $this->setAttribute('autocomplete', 'off');
        $this->setAttribute('data-suggest', $this->Link('suggestions'));
        $this->setAttribute('data-details', $this->Link('details'));

        if (!empty($this->getFieldMap())) {
            $this->setAttribute('data-fields', Convert::raw2att(Convert::raw2json($this->getFieldMap())));
        }

        return parent::Field($properties);
    }

    public function suggestions(SS_HTTPRequest $request)
    {
        if ($request && $request->getVar('q') && !empty($request->getVar('q'))) {
            $query = $request->getVar('q');

            $suggestions = $this->client->getSuggestions($query);

            $response = new SS_HTTPResponse();
            $response->addHeader('Content-Type', 'application/json');

            try {
                $response->setBody(Convert::array2json($suggestions));
            } catch (Exception $e) {
                $response->setBody(Convert::array2json(array(
                    'error' => $e->getMessage()
                )));
            }

            return $response;
        }
    }

    public function details(SS_HTTPRequest $request)
    {
        if ($request && $request->getVar('q') && !empty($request->getVar('q'))) {
            $query = $request->getVar('q');

            $suggestions = $this->client->getDetails($query);

            $response = new SS_HTTPResponse();
            $response->addHeader('Content-Type', 'application/json');

            try {
                $response->setBody(Convert::array2json($suggestions));
            } catch (Exception $e) {
                $response->setBody(Convert::array2json(array(
                    'error' => $e->getMessage()
                )));
            }

            return $response;

        }
    }
}
