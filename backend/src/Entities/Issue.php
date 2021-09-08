<?php
namespace Entities;

use Entities\User;

class Issue{

    public $users_arr = [];
    public $status = ''; //voting, reveal
    public $id = 0;

    public function __construct(int $id = 0, string $status = 'voting', array $users_arr = [])
    {
        $this->id = $id;
        $this->status = $status;
        $this->users_arr = $users_arr;
    }

    public function getMmbers(): array
    {
        $arr = [];
        $data = $this->users_arr;
        foreach ($data as $u) {
            $obj = [];
            $obj['name'] = $u->name;
            $obj['status'] = $u->status;

            if( $this->status != 'voting' && $u->status != 'passed' ){
                $obj['value'] = $u->vote->value;
            }

            $arr[] = $obj;
        }

        return $arr;
    }

    public function checkStatus(): void
    {
        $this->status = 'reveal';
        $cant = count($this->users_arr);
        for ($i=0; $i < $cant; $i++) { 
            $obj = $this->users_arr[$i];
            if($obj->status == 'waiting'){
                $this->status = 'voting';
                return;
            }
        }
    }

    public function calculateAvg(): float
    {
        if($this->status != 'reveal') return -1;
        $cant = count($this->users_arr);
        $sum = 0;
        $tot = 0;

        for ($i=0; $i < $cant; $i++) { 
            $obj = $this->users_arr[$i];
            if($obj->vote->value != -1){
                $tot++;
                $sum += $obj->vote->value;
            }
        }

        if($tot == 0) return -1;
        return $sum / $tot;
    }

    public function permitVote(): bool
    {
        return $this->status == 'voting';
    }

    public function saveInMemory(): void
    {
        $redis = new \Redis();
        $redis->connect('redis', 6379);
        
        $redis->set('issue:'.$this->id.':status', $this->status);
        $redis->set('issue:'.$this->id.':users', json_encode($this->users_arr) );
    }

    public function loadUsersFromMemory(): void
    {
        $this->users_arr = Issues::getUsersForIssue($this->id);
    }

    public function addUser(User $user): void
    {
        $this->users_arr[] = $user;
    }

    public function findUserByName(string $name)
    {
        $cant = count($this->users_arr);
        for ($i=0; $i < $cant; $i++) { 
            $obj = $this->users_arr[$i];
            if($obj->name == $name){
                return $obj;
            }
        }

        return null;
    }



}