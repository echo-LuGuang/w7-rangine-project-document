<?php

namespace W7\App\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use W7\App\Exception\ErrorHttpException;
use W7\App\Model\Logic\DocumentPermissionLogic;
use W7\Core\Middleware\MiddlewareAbstract;

class DocumentPermissionMiddleware extends MiddlewareAbstract {
	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
		$documentId = $request->post('document_id');
		if (!$documentId) {
			throw new ErrorHttpException('Invalid document_id');
		}

		$user = $request->getAttribute('user');
		$documentPermission = (new DocumentPermissionLogic())->getByDocIdAndUid($documentId, $user->id);
		if ($documentPermission) {
			$request = $request->withAttribute('permission', $documentPermission);
		} else {
			throw new ErrorHttpException('无操作权限');
		}

		return parent::process($request, $handler);
	}
}