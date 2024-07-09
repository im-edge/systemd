<?php

namespace IMEdge\systemd;

class NotificationMessage
{
    /**
     * @param array<string,string|int|bool|null> $params
     */
    public function __construct(
        protected array $params
    ) {
    }

    /**
     * Transforms our key/value array into a string like "key1=val1\nkey2=val2"
     */
    public function __toString(): string
    {
        $message = '';
        foreach ($this->params as $key => $value) {
            if ($value === true) {
                $value = Parameter::TRUE;
            } elseif ($value === false) {
                $value = Parameter::FALSE;
            }
            $message .= "$key=$value\n";
        }

        return $message;
    }
}
