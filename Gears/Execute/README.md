# Gears/Execute
A safe, object-oriented way of executing binary files.

## Use
1. Pass the command you want to run (including full path) to the constructor.
2. Call the execute() function to run the command, pass in any information you wanted passed to stdin.
3. Call getOutput() to get the sdtout of the command, or getErrorOutput() to get the stderr.

```php
$command = new Gears\Execute\Execute('ls -Alh');
$output  = $command->execute()->getOutput();
$errors  = $command->getErrorOutput();
```
