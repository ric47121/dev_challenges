<?php
namespace Entities;

use entities\Issues;

class User{

    // public $id;
    // public $votes_arr = [];
    public $vote = null;
    public $name;
    public $status; //waiting, voted, passed

    public function __construct(string $name = '?', string $status = 'waiting')
    {
        $this->name = strtolower($name);
        $this->status = $status;
        $this->vote = new Vote('wait', -1);
    }

    public function updateVote(Vote $vote)
    {
        $this->vote = $vote;
        $this->status = 'voted';
    }


}
