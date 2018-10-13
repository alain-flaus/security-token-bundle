<?php

namespace Yokai\SecurityTokenBundle\Manager;

/**
 * Chained user manager, delegate to other user managers.
 *
 * @author Yann Eugoné <eugone.yann@gmail.com>
 */
class ChainUserManager implements UserManagerInterface
{
    /**
     * @var UserManagerInterface[]
     */
    private $managers;

    /**
     * @param UserManagerInterface[] $managers A list of user managers
     */
    public function __construct(iterable $managers)
    {
        $this->managers = $managers;
    }

    /**
     * @inheritDoc
     */
    public function supportsClass(string $class): bool
    {
        try {
            $this->getManagerForClass($class);
        } catch (\InvalidArgumentException $exception) {
            return false;
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function supportsUser($user): bool
    {
        try {
            $this->getManagerForUser($user);
        } catch (\InvalidArgumentException $exception) {
            return false;
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function get(string $class, string $id)
    {
        return $this->getManagerForClass($class)->get($class, $id);
    }

    /**
     * @inheritDoc
     */
    public function getClass($user): string
    {
        return $this->getManagerForUser($user)->getClass($user);
    }

    /**
     * @inheritDoc
     */
    public function getId($user): string
    {
        return $this->getManagerForUser($user)->getId($user);
    }

    /**
     * Find appropriate user manager for a class.
     *
     * @param string $class The user class
     *
     * @return UserManagerInterface
     * @throws \InvalidArgumentException
     */
    private function getManagerForClass(string $class): UserManagerInterface
    {
        $tries = [];

        foreach ($this->managers as $manager) {
            if ($manager->supportsClass($class)) {
                return $manager;
            }

            $tries[] = get_class($manager);
        }

        throw new \InvalidArgumentException(
            sprintf(
                'Class "%s" is not supported by any UserManager. Tried "%s".',
                $class,
                implode('", "', $tries)
            )
        );
    }

    /**
     * Find appropriate user manager for user.
     *
     * @param mixed $user A user
     *
     * @return UserManagerInterface
     * @throws \InvalidArgumentException
     */
    private function getManagerForUser($user): UserManagerInterface
    {
        $tries = [];

        foreach ($this->managers as $manager) {
            if ($manager->supportsUser($user)) {
                return $manager;
            }

            $tries[] = get_class($manager);
        }

        throw new \InvalidArgumentException(
            sprintf(
                'User "%s" is not supported by any UserManager. Tried "%s".',
                method_exists($user, '__toString') ? $user : get_class($user),
                implode('", "', $tries)
            )
        );
    }
}
