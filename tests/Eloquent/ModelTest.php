<?php
namespace DieZeeL\Database\SphinxConnection\Test\Eloquent;

use DieZeeL\Database\SphinxConnection\Eloquent\Model;
use DieZeeL\Database\SphinxConnection\Test\TestCase;

class ModelMock extends Model
{
    protected $table = 'rt';

    protected $fillable = [
        'id',
        'name',
        'gid',
        'tags',
        'factors'
    ];

    protected $casts = [
        'tags' => 'mva',
        'factors' => 'json',
    ];

    public function getTagsMutatorAttribute()
    {
        return $this->getMvaAttribute('tags');
    }
}

/**
 * Generated by PHPUnit_SkeletonGenerator on 2016-10-08 at 13:16:59.
 */
class ModelTest extends TestCase
{
    /**
     * @var Model
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    public function setUp()
    {
        parent::setUp();

        $this->object = new ModelMock([
            'id' => '1',
            'name' => 'model name',
            'gid' => '12',
            'tags' => '(1, 2, 3)',
            'factors' => json_encode(['var' => 'value'])
        ]);
    }

    /**
     * @covers \DieZeeL\Database\SphinxConnection\Eloquent\Model::getMvaAttribute
     */
    public function testGetMvaAttribute()
    {
        $this->assertInternalType('array', $this->object->tags_mutator);
    }

    /**
     * @covers \DieZeeL\Database\SphinxConnection\Eloquent\Model::asMva
     */
    public function testAsMva()
    {
        $method = new \ReflectionMethod($this->object, 'asMva');
        $method->setAccessible(true);

        $this->assertInternalType('array', $this->object->tags);
        $this->assertNotEmpty(array_filter($this->object->tags, 'is_int'));
        $this->assertArrayHasKey(0, $this->object->tags);
    }

    /**
     * @dataProvider asMvaDataProvider
     * @covers \DieZeeL\Database\SphinxConnection\Eloquent\Model::asMva
     */
    public function testAsMva2($val, $expected)
    {
        $method = new \ReflectionMethod($this->object, 'asMva');
        $method->setAccessible(true);

        $this->assertInternalType('array', $method->invoke($this->object, $val));
        $this->assertEquals($expected, $method->invoke($this->object, $val));

    }

    public function asMvaDataProvider()
    {
        return [
            [[], []],
            ['', []],
            [0, [0]],
            ['0', [0]],
            ['1', [1]],
            ['1,2,3', [1,2,3]],
            ['(1,2,3)', [1,2,3]],
            [[1,2,3], [1,2,3]],
        ];
    }
}
