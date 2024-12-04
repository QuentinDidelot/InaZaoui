<?php

namespace App\Tests\Security;

use App\Entity\User as AppUser;
use App\Security\UserChecker;
use Symfony\Component\Security\Core\Exception\AccountExpiredException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserInterface;
use PHPUnit\Framework\TestCase;

class UserCheckerTest extends TestCase  
{
    private UserChecker $userChecker;

    protected function setUp(): void  
    {
        $this->userChecker = new UserChecker();
    }

    public function testCheckPreAuthThrowsExceptionWhenUserIsRestricted()
    {
        // Créer un mock de AppUser  
        /** @var UserInterface */
        $user = $this->createMock(AppUser::class);
        $user->method('isRestricted')->willReturn(true);
        
        $this->expectException(CustomUserMessageAccountStatusException::class);
        $this->expectExceptionMessage('Votre compte a été suspendu.');
    
        $this->userChecker->checkPreAuth($user);
    }
    
    public function testCheckPreAuthDoesNotThrowExceptionWhenUserIsNotRestricted()
    {
        // Créer un mock de AppUser  
        /** @var UserInterface */
        $user = $this->createMock(AppUser::class);
        $user->method('isRestricted')->willReturn(false);
        
        $this->userChecker->checkPreAuth($user); // Ne doit pas lancer d'exception  
        $this->assertTrue(true); // Juste pour indiquer que le test a réussi  
    }
    
    public function testCheckPostAuthThrowsExceptionWhenUserAccountIsExpired()
    {
        // Créer un mock de AppUser
        /** @var UserInterface */  
        $user = $this->createMock(AppUser::class);
        $user->method('isRestricted')->willReturn(true);
        
        $this->expectException(AccountExpiredException::class);
    
        $this->userChecker->checkPostAuth($user);
    }
    
    public function testCheckPostAuthDoesNotThrowExceptionWhenUserAccountIsNotExpired()
    {
        // Créer un mock de AppUser 
        /** @var UserInterface */
        $user = $this->createMock(AppUser::class);
        $user->method('isRestricted')->willReturn(false);
        
        $this->userChecker->checkPostAuth($user); // Ne doit pas lancer d'exception  
        $this->assertTrue(true); // Juste pour indiquer que le test a réussi  
    }
}