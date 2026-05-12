<?php

declare(strict_types=1);



class QuestionSetShowToUserParams
{
    public ?int $uid;

    public function __construct(?int $uid)
    {
        $this->uid=$uid;
    }

    public function getUid(): ?int
    {
        return $this->uid;
    }
}

?>
