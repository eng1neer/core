<?php

namespace Behat\Behat\Formatter;

use Symfony\Component\EventDispatcher\EventDispatcher;

use Behat\Behat\Event\EventInterface,
    Behat\Behat\Event\FeatureEvent,
    Behat\Behat\Event\ScenarioEvent,
    Behat\Behat\Event\OutlineExampleEvent,
    Behat\Behat\Event\StepEvent;

use Behat\Gherkin\Node\FeatureNode,
    Behat\Gherkin\Node\ScenarioNode,
    Behat\Gherkin\Node\StepNode,
    Behat\Behat\Exception\FormatterException;

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Progress formatter.
 *
 * @author      Konstantin Kudryashov <ever.zet@gmail.com>
 */
class JUnitFormatter extends ConsoleFormatter
{
    /**
     * Current XML filename.
     *
     * @var     string
     */
    protected $filename;
    /**
     * Test cases.
     *
     * @var     array
     */
    protected $testcases = array();
    /**
     * Total steps count.
     *
     * @var     integer
     */
    protected $stepsCount = 0;
    /**
     * Total exceptions count.
     *
     * @var     integer
     */
    protected $exceptionsCount = 0;
    /**
     * Step exceptions.
     *
     * @var     array
     */
    protected $exceptions = array();
    /**
     * Feature start time.
     *
     * @var     float
     */
    protected $featureStartTime;
    /**
     * Scenario start time.
     *
     * @var     float
     */
    protected $scenarioStartTime;

    /**
     * {@inheritdoc}
     */
    public static function getDescription()
    {
        return "Generates a report similar to Ant+JUnit.";
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultParameters()
    {
        return array();
    }

    /**
     * @see     Symfony\Component\EventDispatcher\EventSubscriberInterface::getSubscribedEvents()
     */
    public static function getSubscribedEvents()
    {
        $events = array(
            'beforeFeature', 'afterFeature', 'beforeScenario', 'afterScenario',
            'beforeOutlineExample', 'afterOutlineExample', 'afterStep'
        );

        return array_combine($events, $events);
    }

    /**
     * Listens to "feature.before" event.
     *
     * @param   Behat\Behat\Event\FeatureEvent  $event
     *
     * @uses    printTestSuiteHeader()
     */
    public function beforeFeature(FeatureEvent $event)
    {
        $feature = $event->getFeature();
        $this->filename = 'TEST-' . basename($feature->getFile(), '.feature') . '.xml';

        $this->printTestSuiteHeader($feature);

        $this->stepsCount       = 0;
        $this->testcases        = array();
        $this->exceptionsCount  = 0;
        $this->featureStartTime = microtime(true);
    }

    /**
     * Listens to "feature.after" event.
     *
     * @param   Behat\Behat\Event\FeatureEvent  $event
     *
     * @uses    printTestSuiteFooter()
     * @uses    flushOutputConsole()
     */
    public function afterFeature(FeatureEvent $event)
    {
        $this->printTestSuiteFooter($event->getFeature(), microtime(true) - $this->featureStartTime);
        $this->flushOutputConsole();
    }

    /**
     * Listens to "scenario.before" event.
     *
     * @param   Behat\Behat\Event\ScenarioEvent     $event
     */
    public function beforeScenario(ScenarioEvent $event)
    {
        $this->scenarioStartTime = microtime(true);
    }

    /**
     * Listens to "scenario.after" event.
     *
     * @param   Behat\Behat\Event\ScenarioEvent     $event
     *
     * @uses    printTestCase()
     */
    public function afterScenario(ScenarioEvent $event)
    {
        $this->printTestCase($event->getScenario(), microtime(true) - $this->scenarioStartTime, $event);
    }

    /**
     * Listens to "outline.example.before" event.
     *
     * @param   Behat\Behat\Event\OutlineExampleEvent   $event
     */
    public function beforeOutlineExample(OutlineExampleEvent $event)
    {
        $this->scenarioStartTime = microtime(true);
    }

    /**
     * Listens to "outline.example.after" event.
     *
     * @param   Behat\Behat\Event\OutlineExampleEvent   $event
     *
     * @uses    printTestCase()
     */
    public function afterOutlineExample(OutlineExampleEvent $event)
    {
        $this->printTestCase($event->getOutline(), microtime(true) - $this->scenarioStartTime, $event);
    }

    /**
     * Listens to "step.after" event.
     *
     * @param   Behat\Behat\Event\StepEvent $event
     */
    public function afterStep(StepEvent $event)
    {
        if ($event->hasException()) {
            $this->exceptions[] = $event->getException();
            $this->exceptionsCount++;
        }

        ++$this->stepsCount;
    }

    /**
     * Prints testsuite header.
     *
     * @param   Behat\Gherkin\Node\FeatureNode  $feature
     */
    protected function printTestSuiteHeader(FeatureNode $feature)
    {
        $this->writeln('<?xml version="1.0" encoding="UTF-8"?>');
    }

    /**
     * Prints testsuite footer.
     *
     * @param   Behat\Gherkin\Node\FeatureNode  $feature
     * @param   float                           $time
     */
    protected function printTestSuiteFooter(FeatureNode $feature, $time)
    {
        $suiteStats = sprintf('errors="0" failures="%d" name="%s" file="%s" tests="%d" time="%F"',
            $this->exceptionsCount,
            htmlspecialchars($feature->getTitle()),
            htmlspecialchars($feature->getFile()),
            $this->stepsCount,
            $time
        );

        $this->writeln("<testsuite $suiteStats>");
        $this->writeln(implode("\n", $this->testcases));
        $this->writeln('</testsuite>');
    }

    /**
     * Prints testcase.
     *
     * @param   Behat\Gherkin\Node\ScenarioNode     $feature
     * @param   float                               $time
     * @param   Behat\Behat\Event\EventInterface    $event
     */
    protected function printTestCase(ScenarioNode $scenario, $time, EventInterface $event)
    {
        $className  = $scenario->getFeature()->getTitle();
        $name       = $scenario->getTitle();
        $caseStats  = sprintf('classname="%s" name="%s" time="%F"',
            htmlspecialchars($className),
            htmlspecialchars($name),
            $time
        );

        $xml  = "    <testcase $caseStats>\n";

        foreach ($this->exceptions as $exception) {
            $xml .= sprintf(
                '        <failure message="%s" type="%s">',
                htmlspecialchars($exception->getMessage()),
                $this->getResultColorCode($event->getResult())
            );
            $exception = str_replace(array('<![CDATA[', ']]>'), '', (string) $exception);
            $xml .= "<![CDATA[\n$exception\n]]></failure>\n";
        }
        $this->exceptions = array();

        $xml .= "    </testcase>";

        $this->testcases[] = $xml;
    }

    /**
     * {@inheritdoc}
     */
    protected function createOutputStream()
    {
        $outputPath = $this->parameters->get('output_path');

        if (null === $outputPath) {
            throw new FormatterException(sprintf(
                'You should specify "output_path" parameter for %s', get_class($this)
            ));
        } elseif (is_file($outputPath)) {
            throw new FormatterException(sprintf(
                'Directory path expected as "output_path" parameter of %s, but got: %s',
                get_class($this),
                $outputPath
            ));
        }

        if (!is_dir($outputPath)) {
            mkdir($outputPath, 0777, true);
        }

        return fopen($outputPath . DIRECTORY_SEPARATOR . $this->filename, 'w');
    }
}
