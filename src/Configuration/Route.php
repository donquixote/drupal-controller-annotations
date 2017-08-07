<?php

namespace Drupal\controller_annotations\Configuration;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route as BaseRoute;

/**
 * @Annotation
 */
class Route extends BaseRoute implements RouteModifierInterface
{
    /**
     * @var bool
     */
    protected $admin;

    /**
     * @return bool
     */
    public function isAdmin()
    {
        return $this->admin;
    }

    /**
     * @param bool $admin
     * @return Route
     */
    public function setAdmin($admin)
    {
        $this->admin = $admin;

        return $this;
    }

    /**
     * @param \Symfony\Component\Routing\Route $route
     */
    public function modifyRoute(\Symfony\Component\Routing\Route $route)
    {
        if ($this->isAdmin()) {
            $route->setOption('_admin_route', true);
        }
    }
}
