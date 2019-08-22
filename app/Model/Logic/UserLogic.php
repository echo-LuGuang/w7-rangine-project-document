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
use W7\App\Model\Entity\User;

class UserLogic extends BaseLogic
{
	public function getUserlist($page,$username)
	{
		$res = User::where('username', 'like', '%'.$username.'%')->orderBy('id', 'desc')->get()->toArray();
		if ($res) {
			$this->doclogic = new DocumentLogic();
			return $this->doclogic->paging($this->handleUser($res), 15, $page);
		}
		return $res;
	}

	public function getUser($data)
	{
		if (isset($data['id']) && $data['id']) {
			$user = User::find($data['id']);
		}

		if (isset($data['username']) && $data['username']) {
			$user = User::where('username', $data['username'])->first();
		}
		if (!$user){
			return $user;
		}
		return $user;
	}

	public function createUser($data)
	{
		$users = User::where('username', $data['username'])->count();

		if (!$users) {
			$res = User::create($data);
			$this->handleUser([$res]);
			return $res;
		}
		return false;
	}

	public function updateUser($id, $data)
	{
		ChangeAuthEvent::instance()->attach('user_id', $id)->attach('document_id', 0)->dispatch();
		return User::where('id', $id)->update($data);
	}

	public function detailsUser($id)
	{
		$res = User::find($id);
		if ($res) {
			$res = $this->handleUser([$res]);
			return $res[0];
		}
		return $res;
	}

	public function delUser($ids)
	{
		return User::destroy($ids);
	}

	public function hasDocuments($ids)
	{
		$this->docLogic = new DocumentLogic();

		$i = 0;
		foreach ($ids as $k => $val) {
			$res = $this->docLogic->getUserCreateDoc($val);
			if (!$res) {
				$user = User::find($val);
				if ($user && $user['has_privilege'] != 1) {
					if ($this->delUser($val)) {
						$i++;
					}
				}
			}
		}
		return ['msg'=>'成功删除'.$i.'用户，如果用户有文档不能直接删除'];
	}

	public function handleUser($res)
	{
		if (!$res) {
			return $res;
		}
		foreach ($res as $key => &$val) {
			if (isset($val['has_privilege']) && $val['has_privilege'] == 1) {
				$val['has_privilege_name'] = '有';
			} else {
				$val['has_privilege_name'] = '无';
			}
			if (isset($val['userpass']) && $val['userpass']) {
				unset($val['userpass']);
			}
		}
		return $res;
	}
}