<?php
declare(strict_types=1);

//import basic files now
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/logging.php';

//import repo files they will be used directly here
require_once __DIR__ . '/../../database/repository/userRepository.php';
require_once __DIR__ . '/../../database/repository/userPermissionRepository.php';

require_once __DIR__ . '/../../database/repository/gameConfigRepository.php';

class RegisterUserService
{
    public function registerUserService(?int $uid,string $name,string $email,string $password):array
    {
        $userRepository=new UserRepository();
        $userPermissionRepository=new UserPermissionRepository();

        $userExistingFromEmail=$userRepository->getUserFromEmail($email);

        if ($userExistingFromEmail!==null)
            {
                if ($uid===null || $userExistingFromEmail->getUid()!==$uid)
                    {
                        throw new RuntimeException('A user with this email already exists!!');
                    }
            }


        $gameConfigRepository=new GameConfigRepository();
        $gameConfigCurrent=$gameConfigRepository->getGameConfigFromName(GAME_CONFIG_NAME_DEFAULT);

        if ($gameConfigCurrent===null)
            {
                throw new RuntimeException('Game config was not found!!');
            }

        $secretKey=$gameConfigCurrent->getSecretKey();

        if ($secretKey==='')
            {
                throw new RuntimeException('Secret key is missing in game config!!');
            }

        $passwordWithSecretKey=$this->buildPasswordWithSecretKey($password,$secretKey);

        $passwordHash=password_hash($passwordWithSecretKey,PASSWORD_DEFAULT);

        if ($passwordHash===false)
            {
                throw new RuntimeException('Password hashing failed!!');
            }

        if ($uid!==null)
            {
                $userCurrent=$userRepository->getUserFromUid($uid);

                if ($userCurrent===null)
                    {
                        throw new RuntimeException('Guest user was not found!!');
                    }

                if ($userCurrent->getLoginType()!==USER_LOGIN_TYPE_GUEST)
                    {
                        throw new RuntimeException('Only guest users can be upgraded through this flow!!');
                    }

                $userPermissionCurrentBeforeUpdate=$userPermissionRepository->getUserPermissionFromUid($uid);

                if ($userPermissionCurrentBeforeUpdate===null)
                    {
                        throw new RuntimeException('Guest user permission was not found!!');
                    }

                if ($userPermissionCurrentBeforeUpdate->getPermissionGroup()!==USER_PERMISSION_GROUP_GUEST)
                    {
                        throw new RuntimeException('Only guest users can be upgraded through this flow!!');
                    }


                $userRepository->updateGuestUserToRegistered(
                    $uid,
                    $email,
                    $name,
                    $passwordHash
                );

                $userPermissionRepository->updateUserPermission(
                    $uid,
                    USER_PERMISSION_GROUP_USER
                );

                $userPermissionCurrent=$userPermissionRepository->getUserPermissionFromUid($uid);

                if ($userPermissionCurrent===null)
                    {
                        throw new RuntimeException('User permission was not found!!');
                    }

                Logger::logInfo(
                    'registerUserService',
                    'Guest user upgraded to registered user successfully!!',
                    [
                        'uid'=>$uid,
                        'email'=>$email
                    ]
                );

                return [
                    'uid'=>$uid,
                    'userId'=>$userCurrent->getUserId(),
                    'name'=>$name,
                    'email'=>$email,
                    'loginType'=>USER_LOGIN_TYPE_REGISTERED,
                    'permissionGroup'=>$userPermissionCurrent->getPermissionGroup()
                ];
            }

        $userId=$this->generateRegisteredUserId($userRepository);
        $uidNew=$userRepository->createRegisteredUser($userId,$email,$name,$passwordHash);

        if ($uidNew<=0)
            {
                throw new RuntimeException('Registered user creation failed!!');
            }

        $userPermissionId=$userPermissionRepository->createUserPermission($uidNew,USER_PERMISSION_GROUP_USER);

        if ($userPermissionId<=0)
            {
                throw new RuntimeException('User permission creation failed!!');
            }

        $userCurrent=$userRepository->getUserFromUid($uidNew);

        if ($userCurrent===null)
            {
                throw new RuntimeException('Registered user could not be loaded after creation!!');
            }

        $userPermissionCurrent=$userPermissionRepository->getUserPermissionFromUid($uidNew);

        if ($userPermissionCurrent===null)
            {
                throw new RuntimeException('User permission could not be loaded after creation!!');
            }

        Logger::logInfo(
            'registerUserService',
            'Registered user creation completed successfully!!',
            [
                'uid'=>$userCurrent->getUid(),
                'userId'=>$userCurrent->getUserId(),
                'email'=>$userCurrent->getEmail(),
                'permissionGroup'=>$userPermissionCurrent->getPermissionGroup()
            ]
        );

        return [
            'uid'=>$userCurrent->getUid(),
            'userId'=>$userCurrent->getUserId(),
            'name'=>$userCurrent->getName(),
            'email'=>$userCurrent->getEmail(),
            'loginType'=>$userCurrent->getLoginType(),
            'permissionGroup'=>$userPermissionCurrent->getPermissionGroup()
        ];
    }

    private function generateRegisteredUserId(UserRepository $userRepository):string
    {
        $attemptCount=0;

        while ($attemptCount<USER_ID_GENERATION_ATTEMPT_LIMIT)
            {
                $userId=USER_ID_PREFIX_USER . bin2hex(random_bytes(8));

                if ($userId==='')
                    {
                        throw new RuntimeException('Registered user id generation failed!!');
                    }

                $userCurrent=$userRepository->getUserFromUserId($userId);

                if ($userCurrent===null)
                    {
                        return $userId;
                    }

                $attemptCount++;
            }

        throw new RuntimeException('Registered user id generation exceeded allowed attempts!!');
    }



    private function buildPasswordWithSecretKey(string $password,string $secretKey):string
    {
        return $password . PASSWORD_SECRET_KEY_SEPARATOR . $secretKey;
    }
}
?>