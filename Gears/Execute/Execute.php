<?php
namespace Gears\Execute;

/**
 * Class Execute
 *
 * A safe, object-oriented way of executing binary files.
 *
 * @author    Gregory Benner <Gregbenner1@gmail.com>
 * @copyright 2013
 * @licence   MIT
 */
class Execute
{
    /** @var array[] The proc_open descriptor specs */
    private $descriptors;
    /** @var string The command to run */
    private $command;
    /** @var string The path to the command to run */
    private $path;
    /** @var string The output from the command */
    private $stdout;
    /** @var string The error-output from the command */
    private $stderr;

    /**
     * Pass the command you would like to run into the constructor. It will not be run until you call the run() function.
     * This can be run more than once over the lifetime of the object, however it will overwrite the data from a previous call without any warning.
     *
     * @param string $command Must include full path to command. This should NEVER include user-supplied information. *TREAT THIS LIKE YOU WOULD A SHELL*
     * @param null   $path The path to the command you want to run. This should NEVER include user-supplied information. *TREAT THIS LIKE YOU WOULD A SHELL*
     */
    public function __construct($command, $path = null)
    {
        $this->descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w']
        ];

        $this->command = $command;
        $this->path = $path;
    }

    /**
     * Execute the binary.
     *
     * @param string $data This will be passed verbatim to stdin of the program you are running. Leave it blank for no input.
     *
     * @return $this
     */
    public function run($data = null)
    {
        $pipes = [];

        $handle = proc_open($this->command, $this->descriptors, $pipes, $this->path);

        if (!is_null($data)) {
            fwrite($pipes[0], $data);
            fclose($pipes[0]);
        }

        $this->stdout = stream_get_contents($pipes[1]);
        fclose($pipes[1]);

        $this->stderr = stream_get_contents($pipes[2]);
        fclose($pipes[2]);

        proc_close($handle);

        return $this;
    }

    /**
     * Get the output from the last run() function call.
     *
     * @return mixed The stdout of the program.
     */
    public function getOutput()
    {
        return $this->trimOutput($this->stdout);
    }

    /**
     * Get the error-output of the last run() function call.
     *
     * @return mixed The stderr of the program.
     */
    public function getErrorOutput()
    {
        return $this->trimOutput($this->stderr);
    }
    
    private function trimOutput($output)
    {
        $output = rtrim($output, PHP_EOL);
        
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {//@codeCoverageIgnore
            $output = rtrim($output);//@codeCoverageIgnore
        }//@codeCoverageIgnore
        
        return $output;
    }
}