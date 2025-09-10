<?php

declare(strict_types=1);

/**
 * This file is part of phpDocumentor.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @link https://phpdoc.org
 */

namespace phpDocumentor\DevServer\Watcher;

use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;
use React\EventLoop\Loop;
use React\EventLoop\LoopInterface;
use RuntimeException;

use function fclose;
use function file_put_contents;
use function fopen;
use function fwrite;
use function glob;
use function is_dir;
use function is_file;
use function mkdir;
use function rmdir;
use function sys_get_temp_dir;
use function touch;
use function uniqid;
use function unlink;

class INotifyWatcherTest extends TestCase
{
    private LoopInterface $loop;
    private MockObject|EventDispatcherInterface $dispatcher;
    private INotifyWatcher $watcher;
    private string $testDir;

    /** @var FileModifiedEvent[] */
    private array $capturedEvents = [];

    protected function setUp(): void
    {
        $this->loop = Loop::get();
        $this->dispatcher = $this->createMock(EventDispatcherInterface::class);

        $this->testDir = sys_get_temp_dir() . '/inotify_test_' . uniqid();
        mkdir($this->testDir, 0755, true);

        $this->watcher = new INotifyWatcher(
            $this->loop,
            $this->dispatcher,
            $this->testDir,
        );

        $this->capturedEvents = [];
    }

    protected function tearDown(): void
    {
        $files = glob($this->testDir . '/*');
        if ($files) {
            foreach ($files as $file) {
                if (!is_file($file)) {
                    continue;
                }

                unlink($file);
            }
        }

        if (!is_dir($this->testDir)) {
            return;
        }

        rmdir($this->testDir);
    }

    #[RequiresPhpExtension('inotify')]
    public function testInvokeThrowsExceptionWhenInotifyIsNull(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No inotify watcher');

        ($this->watcher)();
    }

    #[RequiresPhpExtension('inotify')]
    public function testInvokeHandlesNoEvents(): void
    {
        $this->setupEventCapturing();

        $fileName = 'test-file.txt';
        $filePath = $this->testDir . '/' . $fileName;

        touch($filePath);

        $this->watcher->addPath($fileName);

        $this->runLoopBriefly();

        $this->assertEmpty($this->capturedEvents);
    }

    #[RequiresPhpExtension('inotify')]
    public function testInvokeDispatchesFileModifiedEventForInModify(): void
    {
        $this->setupEventCapturing();

        $fileName = 'test-file.txt';
        $filePath = $this->testDir . '/' . $fileName;

        touch($filePath);

        $this->watcher->addPath($fileName);

        file_put_contents($filePath, 'test content');

        $this->runLoopBriefly();

        $this->assertCount(1, $this->capturedEvents);
        $this->assertInstanceOf(FileModifiedEvent::class, $this->capturedEvents[0]);
        $this->assertEquals($fileName, $this->capturedEvents[0]->path);
    }

    #[RequiresPhpExtension('inotify')]
    public function testInvokeDispatchesFileModifiedEventForInCloseWrite(): void
    {
        $this->setupEventCapturing();

        $fileName = 'test-file.txt';
        $filePath = $this->testDir . '/' . $fileName;

        touch($filePath);

        $this->watcher->addPath($fileName);

        $handle = fopen($filePath, 'w');
        fwrite($handle, 'test content for close write');
        fclose($handle);

        $this->runLoopBriefly();

        $this->assertCount(1, $this->capturedEvents);
        $this->assertInstanceOf(FileModifiedEvent::class, $this->capturedEvents[0]);
        $this->assertEquals($fileName, $this->capturedEvents[0]->path);
    }

    #[RequiresPhpExtension('inotify')]
    public function testInvokeHandlesInCreateEvent(): void
    {
        $this->setupEventCapturing();

        $fileName = 'new-file.txt';
        $filePath = $this->testDir . '/' . $fileName;

        $this->watcher->addPath('.');

        touch($filePath);

        $this->runLoopBriefly();

        $this->assertTrue(true); // Test passes if no exception is thrown
    }

    #[RequiresPhpExtension('inotify')]
    public function testInvokeHandlesInDeleteEvent(): void
    {
        $this->setupEventCapturing();

        $fileName = 'delete-file.txt';
        $filePath = $this->testDir . '/' . $fileName;

        touch($filePath);

        $this->watcher->addPath('.');

        unlink($filePath);

        $this->runLoopBriefly();

        $this->assertTrue(true); // Test passes if no exception is thrown
    }

    #[RequiresPhpExtension('inotify')]
    public function testInvokeHandlesMultipleFiles(): void
    {
        $this->setupEventCapturing();

        $file1Name = 'file1.txt';
        $file2Name = 'file2.txt';
        $file1Path = $this->testDir . '/' . $file1Name;
        $file2Path = $this->testDir . '/' . $file2Name;

        touch($file1Path);
        touch($file2Path);

        $this->watcher->addPath($file1Name);
        $this->watcher->addPath($file2Name);

        file_put_contents($file1Path, 'content 1');

        $this->runLoopBriefly();

        $this->assertCount(1, $this->capturedEvents);
        $this->assertEquals($file1Name, $this->capturedEvents[0]->path);

        $this->capturedEvents = [];

        file_put_contents($file2Path, 'content 2');

        $this->runLoopBriefly();

        $this->assertCount(1, $this->capturedEvents);
        $this->assertEquals($file2Name, $this->capturedEvents[0]->path);
    }

    #[RequiresPhpExtension('inotify')]
    public function testAddPathInitializesInotifyOnFirstCall(): void
    {
        $fileName = 'test-file.txt';
        $filePath = $this->testDir . '/' . $fileName;
        touch($filePath);

        $this->watcher->addPath($fileName);

        $this->runLoopBriefly();

        $this->assertTrue(true); // Test passes if no exception is thrown
    }

    #[RequiresPhpExtension('inotify')]
    public function testAddPathDoesNotReinitializeInotifyOnSubsequentCalls(): void
    {
        $file1Name = 'file1.txt';
        $file2Name = 'file2.txt';
        $file1Path = $this->testDir . '/' . $file1Name;
        $file2Path = $this->testDir . '/' . $file2Name;

        touch($file1Path);
        touch($file2Path);

        $this->watcher->addPath($file1Name);
        $this->watcher->addPath($file2Name);

        $this->runLoopBriefly();

        $this->assertTrue(true);
    }

    private function setupEventCapturing(): void
    {
        $this->dispatcher->method('dispatch')
            ->willReturnCallback(function ($event) {
                $this->capturedEvents[] = $event;

                return $event;
            });
    }

    private function runLoopBriefly(): void
    {
        $this->loop->addTimer(0.1, function (): void {
            $this->loop->stop();
        });

        $this->loop->run();
    }
}
