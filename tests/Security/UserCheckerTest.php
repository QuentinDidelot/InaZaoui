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

    public function testCheckPreAuthThrowsExceptionWhenUserIsRestricted(): void
    {
        /** @var \PHPUnit\Framework\MockObject\MockObject&UserInterface */
        $user = $this->createMock(AppUser::class);
        $user->method('isRestricted')->willReturn(true);
        
        $this->expectException(CustomUserMessageAccountStatusException::class);
        $this->expectExceptionMessage('Votre compte a été suspendu.');
    
        $this->userChecker->checkPreAuth($user);
    }
    
    public function testCheckPreAuthDoesNotThrowExceptionWhenUserIsNotRestricted(): void
    {
        /** @var \PHPUnit\Framework\MockObject\MockObject&UserInterface */
        $user = $this->createMock(AppUser::class);
        $user->method('isRestricted')->willReturn(false);
        
        // Ne doit pas lancer d'exception
        $this->userChecker->checkPreAuth($user); 
        
        // Ajouter une assertion explicite pour éviter le test risqué
        $this->addToAssertionCount(1); 
    }
    
    public function testCheckPostAuthThrowsExceptionWhenUserAccountIsExpired(): void
    {
        /** @var \PHPUnit\Framework\MockObject\MockObject&UserInterface */  
        $user = $this->createMock(AppUser::class);
        $user->method('isRestricted')->willReturn(true);
        
        $this->expectException(AccountExpiredException::class);
    
        $this->userChecker->checkPostAuth($user);
    }
    
    public function testCheckPostAuthDoesNotThrowExceptionWhenUserAccountIsNotExpired(): void
    {
        /** @var \PHPUnit\Framework\MockObject\MockObject&UserInterface */
        $user = $this->createMock(AppUser::class);
        $user->method('isRestricted')->willReturn(false);
        
        $this->userChecker->checkPostAuth($user); 
        
        // Ajouter une assertion explicite pour éviter le test risqué
        $this->addToAssertionCount(1); 
    }
}
