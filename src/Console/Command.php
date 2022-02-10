<?php


namespace OSN\Framework\Console;


class Command extends \Symfony\Component\Console\Command\Command
{
    /**
     * The base name of the command.
     *
     * @var string
     */
    protected string $basename;

    /**
     * The regex pattern of the command. This pattern is used to match and get arguments.
     *
     * @var string
     */
    protected string $regex;

    /**
     * The arguments passed to the command.
     *
     * @var string
     */
    protected string $params;

    /**
     * The description of the command.
     *
     * @var string
     */
    protected string $description;

    /**
     * The help text of the command.
     *
     * @var string
     */
    protected string $helpText;

    /**
     * Determines if the command needs to be hidden from the list.
     *
     * @var bool
     */
    protected bool $hidden = false;
}
