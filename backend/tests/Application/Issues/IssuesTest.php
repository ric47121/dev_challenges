<?php
declare(strict_types=1);

namespace Tests\Application\Issues;

use Entities\Issue;
use Entities\User;
use Entities\Issues;
use PHPUnit\Framework\TestCase;

spl_autoload_register(function ($class) {

    $prefix = 'src/';
    $file = $prefix . str_replace('\\', '/', $class) . '.php';

    if (file_exists($file)) {
        require $file;
    }

});

class IssuesTest extends TestCase
{
    public function testPushIssues(): void
    {
        Issues::clearAll();

        $Objissue = new Issue(222);
        $Objissue->status = 'voting';
        Issues::push($Objissue); 

       $this->assertEquals(1, Issues::getCant() );

        $Objissue2 = new Issue(213);
        $Objissue2->status = 'voting';
        Issues::push($Objissue2); 

        $this->assertEquals(2, Issues::getCant() );

        $Objissue3 = new Issue(333);
        $Objissue3->status = 'voting';
        Issues::push($Objissue3); 

       $this->assertEquals(3, Issues::getCant() );

       $this->assertEquals(null, Issues::findById(789) );
       $this->assertEquals($Objissue, Issues::findById(222) );
   
    }

    public function testSaveIssuesInMemory(): void
    {
        Issues::clearAll();
        $this->assertEquals(0, Issues::getCant() );
        
        // --------------------
        $Objissue = new Issue(123);
        $Objissue->status = 'voting';
        Issues::push($Objissue); 

        $user = new User;
        $user->name = "juan";           
        $Objissue->addUser($user);    

        $this->assertEquals(true, $Objissue->permitVote() );
        $this->assertEquals(null, $Objissue->findUserByName('pedro') );
        $this->assertEquals($user, $Objissue->findUserByName('juan') );

        // --------------------
        $Objissue2 = new Issue(456);
        $Objissue2->status = 'voting';
        Issues::push($Objissue2); 

        $user2 = new User;
        $user2->name = "manolo";   
        $Objissue2->addUser($user2);
        // --------------------

        $this->assertEquals( $Objissue2->users_arr, Issues::getUsersForIssue(456));

        Issues::saveIssuesInMemory(); //save json arr
        
        Issues::$issues_arr = [];

        $this->assertEquals(0, Issues::getCant() );

        Issues::loadIssuesFromMemory();
        // var_dump(json_encode(Issues::$issues_arr));
        $this->assertEquals(2, Issues::getCant() );

    }
}

