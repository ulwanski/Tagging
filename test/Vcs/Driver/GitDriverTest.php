<?php
namespace AOE\Tagging\Tests\Vcs\Driver;

use AOE\Tagging\Vcs\Driver\GitDriver;
use Webcreate\Vcs\Common\Reference;

/**
 * @package AOE\Tagging\Tests\Vcs
 */
class GitDriverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldTagAndPush()
    {
        $adapter = $this->getMockBuilder('Webcreate\\Vcs\\Common\\Adapter\\AdapterInterface')
            ->disableOriginalConstructor()
            ->setMethods(array('tag', 'push', 'execute', 'setClient'))
            ->getMock();

        $adapter->expects($this->at(0))->method('execute')->with(
            'tag',
            array('0.2.5'),
            '/home/my/vcs/repo'
        );

        $adapter->expects($this->at(1))->method('execute')->with(
            'push',
            array('origin', 'tag', '0.2.5'),
            '/home/my/vcs/repo'
        );

        $git = $this->getMockBuilder('Webcreate\\Vcs\\Git')
            ->disableOriginalConstructor()
            ->setMethods(array('getAdapter'))
            ->getMock();
        $git->expects($this->exactly(2))->method('getAdapter')->will($this->returnValue($adapter));

        $driver = $this->getMockBuilder('AOE\\Tagging\\Vcs\\Driver\\GitDriver')
            ->disableOriginalConstructor()
            ->setMethods(array('getGit'))
            ->getMock();

        $driver->expects($this->exactly(2))->method('getGit')->will(
            $this->returnValue($git)
        );

        /** @var GitDriver $driver */
        $driver->tag('0.2.5', '/home/my/vcs/repo');
    }

    /**
     * @test
     * @dataProvider references
     */
    public function shouldGetLatestVersion(array $references)
    {
        $git = $this->getMockBuilder('Webcreate\\Vcs\\Git')
            ->disableOriginalConstructor()
            ->setMethods(array('tags'))
            ->getMock();
        $git->expects($this->once())->method('tags')->will($this->returnValue($references));

        $driver = $this->getMockBuilder('AOE\\Tagging\\Vcs\\Driver\\GitDriver')
            ->disableOriginalConstructor()
            ->setMethods(array('getGit'))
            ->getMock();

        $driver->expects($this->once())->method('getGit')->will(
            $this->returnValue($git)
        );

        /** @var GitDriver $driver */
        $this->assertEquals('0.12.0', $driver->getLatestTag());
    }

    /**
     * @return array
     */
    public function references()
    {
        return array(
            'normal order' => array(
                array(
                    new Reference('0.1.0'),
                    new Reference('0.2.0'),
                    new Reference('0.3.0'),
                    new Reference('0.4.0'),
                    new Reference('0.5.0'),
                    new Reference('0.6.0'),
                    new Reference('0.7.0'),
                    new Reference('0.8.0'),
                    new Reference('0.9.0'),
                    new Reference('0.10.0'),
                    new Reference('0.11.0'),
                    new Reference('0.12.0')
                )
            ),
            'weird order' => array(
                array(
                    new Reference('0.10.0'),
                    new Reference('0.11.0'),
                    new Reference('0.12.0'),
                    new Reference('0.1.0'),
                    new Reference('0.2.0'),
                    new Reference('0.3.0'),
                    new Reference('0.4.0'),
                    new Reference('0.5.0'),
                    new Reference('0.6.0'),
                    new Reference('0.7.0'),
                    new Reference('0.8.0'),
                    new Reference('0.9.0')
                )
            )
        );
    }
}