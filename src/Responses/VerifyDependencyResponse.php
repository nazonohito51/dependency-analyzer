<?php
declare(strict_types=1);

namespace DependencyAnalyzer\Responses;

class VerifyDependencyResponse implements \Countable
{
    /**
     * @var string
     */
    private $ruleName;

    private $violations = [];

    public function __construct(string $ruleName)
    {
        $this->ruleName = $ruleName;
    }

    public function addRuleViolation(string $dependerComponent, string $depender, string $dependeeComponent, string $dependee)
    {
        $this->violations[] = [
            'dependerComponent' => $dependerComponent,
            'depender' => $depender,
            'dependeeComponent' => $dependeeComponent,
            'dependee' => $dependee
        ];
    }

    public function getRuleName()
    {
        return $this->ruleName;
    }

    public function getViolations(): array
    {
        return $this->violations;
        /**
         * Hoge component rule violations
         * | hoge.php | Hoge | -> | fuga.php | Fuga |
         * | hoge.php | Hoge | -> | fuga.php | Fuga |
         *
         * | No | file path |
         * |---|---|
         * | 1  | hoge.php |
         * | 2  | fuga.php |
         */
    }

    public function count()
    {
        return count($this->violations);
    }
}
