<?php

namespace Lagdo\PolrAdmin\Helpers;

use Valitron\Validator as ValitronValidator;

class Validator
{
    /**
     * Validate dates for stats
     *
     * @param array $dates
     *
     * @return boolean
     */
    public function validateStatsDate(array $dates)
    {
        $validator = new ValitronValidator($dates);
        $validator->rule('date', ['right_bound', 'left_bound']);
        return $validator->validate();
    }

    /**
     * Validate link ending
     *
     * @param string $ending
     *
     * @return boolean
     */
    public function validateLinkEnding($ending)
    {
        $values = [
            'ending' => $ending,
        ];
        $rules = array(
            'ending' => [['required'], ['slug']],
        );
        $validator = new ValitronValidator($values);
        $validator->mapFieldsRules($rules);
        return $validator->validate();
    }

    /**
     * Validate link
     *
     * @param array $values
     * @param boolean $endingRequired
     *
     * @return boolean
     */
    public function validateLinkUrl(array $values, $endingRequired)
    {
        $rules = array(
            'ending' => [['slug']],
            'url' => [['url']],
            'status' => [['in', ['enable', 'disable']]],
        );
        if(($endingRequired))
        {
            $rules['ending'][] = ['required'];
        }
        $validator = new ValitronValidator($values);
        $validator->mapFieldsRules($rules);
        return $validator->validate();
    }

    /**
     * Validate end point name
     *
     * @param string $server
     *
     * @return boolean
     */
    public function validateServer($server)
    {
        $values = [
            'server' => $server,
        ];
        $rules = array(
            'server' => [['required'], ['slug']],
        );
        $validator = new ValitronValidator($values);
        $validator->mapFieldsRules($rules);
        return $validator->validate();
    }

    /**
     * Validate an id (integer)
     *
     * @param integer $id
     *
     * @return boolean
     */
    public function validateId($id)
    {
        $values = ['id' => $id];
        $rules = [
            'id' => [['required'], ['integer']],
        ];
        $validator = new ValitronValidator($values);
        $validator->mapFieldsRules($rules);
        return $validator->validate();
    }

    /**
     * Validate user quota
     *
     * @param array $values
     *
     * @return boolean
     */
    public function validateUserQuota(array $values)
    {
        $rules = [
            'id' => [['required'], ['integer']],
            'quota' => [['required'], ['integer']],
        ];
        $validator = new ValitronValidator($values);
        $validator->mapFieldsRules($rules);
        return $validator->validate();
    }

    /**
     * Validate user status
     *
     * @param array $values
     *
     * @return boolean
     */
    public function validateUserStatus(array $values)
    {
        $rules = [
            'id' => [['required'], ['integer']],
            'status' => [['required'], ['integer'], ['in', [0, 1]]],
        ];
        $validator = new ValitronValidator($values);
        $validator->mapFieldsRules($rules);
        return $validator->validate();
    }

    /**
     * Validate user role
     *
     * @param array $values
     *
     * @return boolean
     */
    public function validateUserRole(array $values)
    {
        $rules = [
            'id' => [['required'], ['integer']],
            'role' => [['required'], ['alphaNum'], ['lengthBetween', 1, 16]],
        ];
        $validator = new ValitronValidator($values);
        $validator->mapFieldsRules($rules);
        return $validator->validate();
    }
}
