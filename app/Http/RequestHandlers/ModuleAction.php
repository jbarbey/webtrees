<?php
/**
 * webtrees: online genealogy
 * Copyright (C) 2019 webtrees development team
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */
declare(strict_types=1);

namespace Fisharebest\Webtrees\Http\RequestHandlers;

use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\Contracts\UserInterface;
use Fisharebest\Webtrees\Services\ModuleService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use function method_exists;
use function strpos;
use function strtolower;

/**
 * Controller for module actions.
 */
class ModuleAction implements RequestHandlerInterface
{
    /** @var ModuleService */
    private $module_service;

    /** @var UserInterface */
    private $user;

    /**
     * ModuleController constructor.
     *
     * @param ModuleService $module_service
     * @param UserInterface $user
     */
    public function __construct(ModuleService $module_service, UserInterface $user)
    {
        $this->module_service = $module_service;
        $this->user           = $user;
    }

    /**
     * Perform an HTTP action for one of the modules.
     *
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $module_name = $request->getQueryParams()['module'] ?? $request->getParsedBody()['module'] ?? '';
        $action      = $request->getQueryParams()['action'] ?? $request->getParsedBody()['action'] ?? '';

        // Check that the module is enabled.
        // The module itself will need to check any tree-level access,
        // which may be different for each component (tab, menu, etc.) of the module.
        $module = $this->module_service->findByName($module_name);

        if ($module === null) {
            throw new NotFoundHttpException('Module ' . $module_name . ' does not exist');
        }

        // We'll call a function such as Module::getFooBarAction()
        $verb   = strtolower($request->getMethod());
        $method = $verb . $action . 'Action';

        // Actions with "Admin" in the name are for administrators only.
        if (strpos($action, 'Admin') !== false && !Auth::isAdmin($this->user)) {
            throw new AccessDeniedHttpException('Admin only action');
        }

        if (!method_exists($module, $method)) {
            throw new NotFoundHttpException('Method ' . $method . '() not found in ' . $module_name);
        }

        return $module->$method($request);
    }
}
