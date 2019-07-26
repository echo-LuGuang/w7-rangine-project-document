<?php

/**
 * WeEngine Document System
 *
 * (c) We7Team 2019 <https://www.w7.cc>
 *
 * This is not a free software
 * Using it under the license terms
 * visited https://www.w7.cc for more details
 */

namespace W7\App\Model\Logic;

use W7\App\Event\ChangeAuthEvent;
use W7\App\Model\Entity\Category;
use W7\App\Model\Entity\Document;
use W7\App\Model\Entity\User;
use W7\App\Model\Entity\UserAuthorization;

class UserAuthorizationLogic extends BaseLogic
{
	public function inviteUser($user_id, $document_id)
	{
		$exist = UserAuthorization::where('user_id',$user_id)->where('document_id',$document_id)->first();
		if($exist){
			throw new \Exception('该用户已经拥有操作该文档的权限!');
		}
		$result = UserAuthorization::create(['user_id' => $user_id,'document_id' => $document_id]);
		ChangeAuthEvent::instance()->attach('user_id',$user_id)->attach('document_id',$document_id)->dispatch();
		return $result;
	}

	public function leaveDocument($user_id, $document_id)
	{
		UserAuthorization::where('user_id',$user_id)->where('document_id',$document_id)->delete();
		ChangeAuthEvent::instance()->attach('user_id',$user_id)->attach('document_id',$document_id)->dispatch();
		return true;
	}

	public function getUserAuthorizations($user_id)
	{
		$cacheAuth = icache()->get('auth_'.$user_id);
		if ($cacheAuth) {
			return $cacheAuth;
		}
		$user = User::find($user_id);
		if ($user) {
			if ($user->has_privilege) {
				icache()->set('auth_'.$user_id, APP_AUTH_ALL,24*3600);
				return APP_AUTH_ALL;
			}
		} else {
			return [];
		}
		$auth['document'] = UserAuthorization::where('user_id', $user_id)->pluck('document_id')->toArray();
		icache()->set('auth_'.$user_id, $auth,24*3600);
		return $auth;
	}

	public function getDocumentUsers($document_id)
	{
		$cacheDocumentUsers = icache()->get('document_users_'.$document_id);
		if($cacheDocumentUsers){
			return $cacheDocumentUsers;
		}
		$documentUsers = UserAuthorization::where('document_id',$document_id)->pluck('user_id')->toArray();
		icache()->set('document_users_'.$document_id,$documentUsers,24*3600);
		return $documentUsers;
	}


}
