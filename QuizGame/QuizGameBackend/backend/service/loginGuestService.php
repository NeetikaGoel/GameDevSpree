<?php
declare(strict_types=1);

//import basic files now
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/logging.php';

//import repo files they will be used directly here
require_once __DIR__ . '/../../database/repository/userRepository.php';
require_once __DIR__ . '/../../database/repository/userPermissionRepository.php';


class LoginGuestService
{
    public function loginGuestService():array
    {
        //initialize both db repos first
        $userRepository=new UserRepository();
        $userPermissionRepository=new UserPermissionRepository();
        
        //great now next step can be what
        //yes lets generate a new id
        $userId=$this->generateGuestUserId($userRepository);

        //add that newly generated id in the database now
        $uid=$userRepository->createGuestUser($userId);

        if ($uid<=0)
            {
                throw new RuntimeException('Guest user creation failed!!');
            }

        //create the entry of this guest user in permission table as well
        $userPermissionId=$userPermissionRepository->createUserPermission($uid,USER_PERMISSION_GROUP_GUEST);

        if ($userPermissionId<=0)
            {
                throw new RuntimeException('Guest permission creation failed!!');
            }

        //now fetching the user from the database
        $userCurrent=$userRepository->getUserFromUid($uid);

        //what if got nothing
        if ($userCurrent===null)
            {
                throw new RuntimeException('Guest user could not be loaded after creation!!');
            }

        //great so now we have that user
        //do the same for permission table
        $userPermissionCurrent=$userPermissionRepository->getUserPermissionFromUid($uid);

        if ($userPermissionCurrent===null)
            {
                throw new RuntimeException('Guest permission could not be loaded after creation!!');
            }

        //now we have everything we need
        //now log it dear
        Logger::logInfo(
            'loginGuestService',
            'Guest login completed successfully!!',
            [
                'uid'=>$userCurrent->getUid(),
                'userId'=>$userCurrent->getUserId(),
                'loginType'=>$userCurrent->getLoginType(),
                'permissionGroup'=>$userPermissionCurrent->getPermissionGroup()
            ]
        );

        //lets just return it now
        return [
            'uid'=>$userCurrent->getUid(),
            'userId'=>$userCurrent->getUserId(),
            'loginType'=>$userCurrent->getLoginType(),
            'permissionGroup'=>$userPermissionCurrent->getPermissionGroup()
        ];
    }
    
    //oh i totally forgot abt generate hehe
    //so here it is now!!
    private function generateGuestUserId(UserRepository $userRepository):string
    {
        $attemptCount=0;

        while ($attemptCount<USER_ID_GENERATION_ATTEMPT_LIMIT)
            {
                $userId=USER_ID_PREFIX_GUEST . bin2hex(random_bytes(8)); //this bin2hex is used to generate a secure, random, 16 char hexadecimal string now good good !!!!

                //some error my god
                if ($userId==='')
                    {
                        throw new RuntimeException('Guest user id generation failed!!');
                    }

                $userCurrent=$userRepository->getUserFromUserId($userId);

                if ($userCurrent===null)
                    {
                        return $userId;
                    }

                $attemptCount++;
            }

        throw new RuntimeException('Guest user id generation exceeded allowed attempts!!');
    }
}
?>