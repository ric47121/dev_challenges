<?php
declare(strict_types=1);

namespace Tests\Application\Votes;

use Entities\Issue;
use Entities\User;
use Entities\Vote;
use Entities\Issues;
use PHPUnit\Framework\TestCase;

spl_autoload_register(function ($class) {

    $prefix = 'src/';
    $file = $prefix . str_replace('\\', '/', $class) . '.php';

    if (file_exists($file)) {
        require $file;
    }

});

class VotesTest extends TestCase
{
    public function testPermitVotes(): void
    {
        Issues::clearAll();

        $Objissue = new Issue(222);
        $Objissue->status = 'voting';
        Issues::push($Objissue); 

        $user = new User;
        $user->name = "juan";           
        $Objissue->addUser($user);   

        $user2 = new User;
        $user2->name = "manolo";           
        $Objissue->addUser($user2);   

        $vote = new Vote('wait', -1);
        $user->updateVote($vote);
        $user->status = 'waiting';

        $this->assertEquals(true, $Objissue->permitVote() );
        $this->assertEquals(null, $Objissue->findUserByName('pedro'));
        $this->assertEquals($user, $Objissue->findUserByName('juan'));

        // user 1 votes but user 2 does not
        $vote = new Vote('voted', 15);
        $user->updateVote($vote); 

        $this->assertEquals(false, $user->permitVote() );
        $this->assertEquals(true, $user2->permitVote() );

        $Objissue->checkStatus();
        $this->assertEquals(true, $Objissue->permitVote() );

        // user 1 and user 2 vote
        $vote = new Vote('voted', 9);
        $user2->updateVote($vote); 

        $Objissue->checkStatus();

        // the issue does not allow more votes
        $this->assertEquals(false, $Objissue->permitVote() );


   
    }

}