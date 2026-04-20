<?php
declare(strict_types=1);

//importing basic files now
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/logging.php';

//importing repo files they will be used directly here
require_once __DIR__ . '/../../database/repository/userRepository.php';
require_once __DIR__ . '/../../database/repository/userPermissionRepository.php';

require_once __DIR__ . '/../../database/repository/gameConfigRepository.php';


class LoginUserService
{
    public function loginUserService(string $email,string $password):array
    {
        $userRepository=new UserRepository();
        $userPermissionRepository=new UserPermissionRepository();

        $userCurrent=$userRepository->getUserFromEmail($email);

        if ($userCurrent===null)
            {
                throw new InvalidArgumentException('Registered user was not found!!');
            }

        $passwordHashCurrent=$userCurrent->getPasswordHash();

        if ($passwordHashCurrent===null || $passwordHashCurrent==='')
            {
                throw new InvalidArgumentException('Password is incorrect!!');
            }

        $gameConfigRepository=new GameConfigRepository;
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

        if (!password_verify($passwordWithSecretKey,$passwordHashCurrent))
            {
                throw new InvalidArgumentException('Password is incorrect!!');
            }


        $userPermissionCurrent=$userPermissionRepository->getUserPermissionFromUid($userCurrent->getUid());

        if ($userPermissionCurrent===null)
            {
                throw new RuntimeException('User permission was not found!!');
            }

        Logger::logInfo(
            'loginUserService',
            'User login completed successfully!!',
            [
                'uid'=>$userCurrent->getUid(),
                'userId'=>$userCurrent->getUserId(),
                'loginType'=>$userCurrent->getLoginType(),
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


    private function buildPasswordWithSecretKey(string $password,string $secretKey):string
    {
        return $password . PASSWORD_SECRET_KEY_SEPARATOR . $secretKey;
    }
}
?>