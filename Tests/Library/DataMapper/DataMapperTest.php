<?php

namespace Tests\Library\DataMapper;

use Carbon\Carbon;
use Library\DataMapper\Proxy\ProxyEntity;
use Tests\TestData\DataMapper\TimeEntity;
use Library\DataMapper\Collection\PersistentCollection;
use Library\DataMapper\Collection\EntityCollection;
use Tests\TestData\DataMapper\Address;
use Tests\TestData\DataMapper\Event;
use Tests\TestData\DataMapper\Lesson;
use Tests\TestData\DataMapper\SimpleEntity;
use Tests\TestData\DataMapper\Student;
use Tests\TestData\DataMapper\Teacher;

class DataMapperTest extends DataMapperBaseTest
{
    public function testPersistWhenInserting()
    {
        // Arrange
        $this->setUpSimpleEntity();
        $s1 = new SimpleEntity(1, 2, '1', '2');
        $this->dm->persist($s1);

        // Act
        $this->dm->flush();

        // Assert
        $this->assertNotNull($s1->getId());
    }

    public function testPersistWhenInsertingMultipleNewEntities()
    {
        // Arrange
        $this->setUpSimpleEntity();
        $entities = [];
        for ($i = 0; $i < 10; $i++)
        {
            $entity = new SimpleEntity($i, 2, '1', '2');
            $entities[] = $entity;
            $this->dm->persist($entity);
        }

        // Act
        $this->dm->flush();

        // Assert
        $this->assertNotNull($entities[0]->getId());
        $startId = $entities[0]->getId();
        for ($i = 0; $i < 10; $i++)
        {
            $this->assertEquals($startId + $i, $entities[$i]->getId());
        }
    }

    public function testFindWhenEntityIsManaged()
    {
        // Arrange
        $this->setUpSimpleEntity();
        $s1 = new SimpleEntity(1, 2, '1', '2');
        $this->dm->persist($s1);
        $this->dm->flush();

        // Act
        $foundS1 = $this->dm->find(SimpleEntity::class, $s1->getId());

        // Assert
        $this->assertTrue($s1 === $foundS1);
    }

    public function testFindWhenEntityIsNotManaged()
    {
        // Arrange
        $this->setUpSimpleEntity();
        $s1 = new SimpleEntity(1, 2, '1', '2');
        $this->dm->persist($s1);
        $this->dm->flush();

        // Act
        $this->dm->detachAll();
        $foundS1 = $this->dm->find(SimpleEntity::class, $s1->getId());

        // Assert
        $this->assertFalse($s1 === $foundS1);
        $this->assertEquals($s1->getId(), $foundS1->getId());
    }

    public function testFindWhenNotFound()
    {
        // Arrange
        $this->setUpSimpleEntity();

        // Act
        $found = $this->dm->find(SimpleEntity::class, 9999);

        // Assert
        $this->assertNull($found);
    }

    /**
     * @expectedException Exception
     */
    public function testFindOrFailWhenNotFound()
    {
        // Arrange
        $this->setUpSimpleEntity();

        // Act
        $found = $this->dm->findOrFail(SimpleEntity::class, 9999);

        // Assert
        $this->assertNull($found);
    }

    public function testFindAll()
    {
        // Arrange
        $this->setUpSimpleEntity();
        $this->dm->persist(new SimpleEntity(1, 2, 'one', 'two'));
        $this->dm->persist(new SimpleEntity(11, 12, 'one2', 'two2'));
        $this->dm->persist(new SimpleEntity(21, 22, 'one3', 'two3'));
        $this->dm->flush();

        // Act
        $collection = $this->dm->findAll(SimpleEntity::class);

        // Assert
        $this->assertTrue($collection instanceof EntityCollection);
        $this->assertEquals(3, $collection->count());
        $this->assertTrue($collection->first() instanceof SimpleEntity);
    }

    public function testFindIn()
    {
        // Arrange
        $this->setUpSimpleEntity();
        $ids = [];
        for ($i = 0; $i < 20; $i++)
        {
            $entity = new SimpleEntity($i, 2, 'one', 'two');
            $this->dm->persist($entity);
            $this->dm->flush();
            $id = $entity->getId();
            if ($i > 10 && $i < 16)
            {
                $ids[] = $id;
            }
        }

        // Act
        $collection = $this->dm->findIn(SimpleEntity::class, $ids);

        // Assert
        $this->assertTrue($collection instanceof EntityCollection);
        $this->assertEquals(5, $collection->count());
        $this->assertTrue($collection->first() instanceof SimpleEntity);
    }

    public function testFindBy()
    {
        // Arrange
        $this->setUpSimpleEntity();
        $se1 = new SimpleEntity(11, 12, 'one', 'two');
        $this->dm->persist($se1);
        $se2 = new SimpleEntity(11, 12, 'one2', 'two2');
        $this->dm->persist($se2);
        $se3 = new SimpleEntity(11, 12, 'one2', 'two2');
        $this->dm->persist($se3);
        $this->dm->flush();

        // Act
        $collection = $this->dm->findBy(SimpleEntity::class, ['one' => 11, 'str1' => 'one']);

        // Assert
        $this->assertTrue($collection instanceof EntityCollection);
        $this->assertEquals(1, $collection->count());
        $this->assertEquals(11, $collection->first()->getOne());
    }

    /**
     * @expectedException Exception
     */
    public function testFindByWhenPropertyDoesNotExist()
    {
        // Arrange
        $this->setUpSimpleEntity();

        // Act
        $collection = $this->dm->findBy(SimpleEntity::class, ['nonexistant' => 10]);

        // Assert
        $this->assertNull($collection);
    }

    public function testFindByWhenNotFound()
    {
        // Arrange
        $this->setUpSimpleEntity();

        // Act
        $collection = $this->dm->findBy(SimpleEntity::class, ['one' => 9999]);

        // Assert
        $this->assertTrue($collection instanceof EntityCollection);
        $this->assertEquals(0, $collection->count());
    }

    public function testFindOneBy()
    {
        // Arrange
        $this->setUpSimpleEntity();
        $se1 = new SimpleEntity(1, 2, 'one', 'two');
        $this->dm->persist($se1);
        $se2 = new SimpleEntity(2, 12, 'one2', 'two2');
        $this->dm->persist($se2);
        $this->dm->flush();

        // Act
        $entitiy = $this->dm->findOneBy(SimpleEntity::class, ['one' => 1, 'str1' => 'one']);

        // Assert
        $this->assertTrue($entitiy instanceof SimpleEntity);
        $this->assertEquals($se1->getOne(), $entitiy->getOne());
    }

    public function testDelete()
    {
        // Arrange
        $this->setUpSimpleEntity();
        $s1 = new SimpleEntity(1, 2, '1', '2');
        $this->dm->persist($s1);
        $this->dm->flush();

        // Act
        $this->dm->delete($s1);
        $this->dm->flush();
        $foundS1 = $this->dm->find(SimpleEntity::class, $s1->getId());

        // Assert
        $this->assertNull($foundS1);
    }

    /**
     * @expectedException Exception
     */
    public function testDeleteWhenEntityIsUnknown()
    {
        // Arrange
        $this->setUpSimpleEntity();
        $s1 = new SimpleEntity(1, 2, '1', '2');

        // Act
        $this->dm->delete($s1);
        $this->dm->flush();
    }

    public function testDeleteMultiple()
    {
        // Arrange
        $this->setUpSimpleEntity();
        $s1 = new SimpleEntity(1, 2, '1', '2');
        $s2 = new SimpleEntity(2, 2, '1', '2');
        $s3 = new SimpleEntity(3, 2, '1', '2');
        $this->dm->persist($s1);
        $this->dm->persist($s2);
        $this->dm->persist($s3);
        $this->dm->flush();

        // Act
        $this->dm->delete($s1);
        $this->dm->delete($s2);
        $this->dm->delete($s3);
        $this->dm->flush();
        $foundS1 = $this->dm->find(SimpleEntity::class, $s1->getId());
        $foundS2 = $this->dm->find(SimpleEntity::class, $s2->getId());
        $foundS3 = $this->dm->find(SimpleEntity::class, $s3->getId());

        // Assert
        $this->assertNull($foundS1);
        $this->assertNull($foundS2);
        $this->assertNull($foundS3);
    }

    public function testUpdate()
    {
        // Arrange
        $this->setUpSimpleEntity();
        $s1 = new SimpleEntity(1, 2, '1', '2');
        $this->dm->persist($s1);
        $this->dm->flush();

        // Act
        $s1->setOne(10);
        $this->dm->flush();
        $this->dm->detachAll();
        $foundS1 = $this->dm->find(SimpleEntity::class, $s1->getId());

        // Assert
        $this->assertEquals($foundS1->getOne(), $s1->getOne());
    }

    public function testUpdateForMultipleWithSameFields()
    {
        // Arrange
        $this->setUpSimpleEntity();
        $s1 = new SimpleEntity(1, 2, '1', '2');
        $s2 = new SimpleEntity(2, 2, '1', '2');
        $s3 = new SimpleEntity(3, 2, '1', '2');
        $this->dm->persist($s1);
        $this->dm->persist($s2);
        $this->dm->persist($s3);
        $this->dm->flush();

        // Act
        $s1->setOne(10);
        $s2->setOne(11);
        $s3->setOne(12);
        $this->dm->flush();
        $this->dm->detachAll();
        $foundS1 = $this->dm->find(SimpleEntity::class, $s1->getId());
        $foundS2 = $this->dm->find(SimpleEntity::class, $s2->getId());
        $foundS3 = $this->dm->find(SimpleEntity::class, $s3->getId());

        // Assert
        $this->assertEquals($s1->getOne(), $foundS1->getOne());
        $this->assertEquals($s2->getOne(), $foundS2->getOne());
        $this->assertEquals($s3->getOne(), $foundS3->getOne());
    }

    public function testUpdateForMultipleWithDifferentFields()
    {
        // Arrange
        $this->setUpSimpleEntity();
        $s1 = new SimpleEntity(1, 2, '1', '2');
        $s2 = new SimpleEntity(2, 2, '1', '2');
        $s3 = new SimpleEntity(3, 2, '1', '2');
        $this->dm->persist($s1);
        $this->dm->persist($s2);
        $this->dm->persist($s3);
        $this->dm->flush();

        // Act
        $s1->setOne(10);
        $s2->setOne(11);
        $s3->setTwo(12);
        $this->dm->flush();
        $this->dm->detachAll();
        $foundS1 = $this->dm->find(SimpleEntity::class, $s1->getId());
        $foundS2 = $this->dm->find(SimpleEntity::class, $s2->getId());
        $foundS3 = $this->dm->find(SimpleEntity::class, $s3->getId());

        // Assert
        $this->assertEquals($foundS1->getOne(), $s1->getOne());
        $this->assertEquals($foundS2->getOne(), $s2->getOne());
        $this->assertEquals($foundS3->getOne(), $s3->getOne());
        $this->assertEquals($foundS3->getTwo(), $s3->getTwo());
    }

    /*
     * DATETIME TESTS
     */

    public function testDateTimePropertyIsAlwaysHydratedAsCarbonInstance()
    {
        // Arrange
        $this->setUpTimeEntity();
        $a = new TimeEntity(Carbon::now());
        $this->dm->persist($a);
        $this->dm->flush();
        $this->dm->detachAll();

        // Act
        $a = $this->dm->find(TimeEntity::class, $a->getId());

        // Assert
        $this->assertNotNull($a);
        $this->assertTrue($a->getCreatedAt() instanceof Carbon);
        $this->assertTrue($a->getUpdatedAt() instanceof Carbon);
        $this->assertTrue($a->date() instanceof Carbon);
    }

    /*
     * HAS ONE INSERTS
     */

    public function testHasOneWhenInsertingIgnoresAssociation()
    {
        // Arrange
        $this->setUpAssociations();
        $teacher = new Teacher('Tom');
        $address = new Address('street1');

        // Act
        $teacher->setAddress($address);
        $this->dm->persist($teacher);
        $this->dm->flush();
        $teacherData = $this->dm->queryBuilder()->table('teacher')->where('id', '=', $teacher->getId())->select()[0];
        $addressData = $this->dm->queryBuilder()->table('address')->where('id', '=', $address->getId())->select();

        // Assert
        $this->assertEquals(0, sizeof($addressData));
        $this->assertGreaterThan(0, sizeof($teacherData));
    }

    /*
     * HAS ONE UPDATES
     */

    public function testHasOneWhenUpdatingIgnoresAssociationChangesWhenAssigningEntity()
    {
        // Arrange
        $this->setUpAssociations();
        $address = new Address('street1');
        $this->dm->persist($address);
        $this->dm->flush();

        // Act
        $teacher = new Teacher('Tom');
        $this->dm->persist($teacher);
        $teacher->setAddress($address);
        $this->dm->flush();
        $addressData = $this->dm->queryBuilder()->table('address')->where('id', '=', $address->getId())->select()[0];

        // Assert
        $this->assertEquals($address->getId(), $addressData['id']);
        $this->assertNotEquals($teacher->getId(), $addressData['teacher_id']);
    }

    /*
     * HAS ONE REMOVALS
     */

    public function testHasOneWhenDeletingEntityWithCascadeDeleteEnabled()
    {
        // Arrange
        $this->setUpAssociations();
        $teacher = new Teacher('Tom');
        $this->dm->persist($teacher);
        $address = new Address('street1');
        $address->setTeacher($teacher);
        $this->dm->persist($address);
        $this->dm->flush();
        $teacher->setAddress($address);

        // Act
        $this->dm->delete($teacher);
        $this->dm->flush();
        $teacherData = $this->dm->queryBuilder()->table('teacher')->where('id', '=', $teacher->getId())->select();
        $addressData = $this->dm->queryBuilder()->table('address')->where('id', '=', $address->getId())->select();

        // Assert
        $this->assertEquals(0, sizeof($addressData));
        $this->assertEquals(0, sizeof($teacherData));
    }

    /*
     * HAS ONE FIND
     */

    public function testHasOneWhenFindingByIdDetachedEntitiesWithNullChildEntity()
    {
        // Arrange
        $this->setUpAssociations();
        $teacher = new Teacher('Tom');
        $this->dm->persist($teacher);
        $this->dm->flush();

        // Act
        $this->dm->detachAll();
        $foundTeacher = $this->dm->find(Teacher::class, $teacher->getId());

        // Assert
        $this->assertEquals($teacher->getId(), $foundTeacher->getId());
        $this->assertTrue(is_null($foundTeacher->address()) || $foundTeacher->address() instanceof ProxyEntity);
    }

    public function testHasOneWhenFindingByIdDetachedEntitiesWithExisitingChildEntity()
    {
        // Arrange
        $this->setUpAssociations();
        $teacher = new Teacher('Tom');
        $this->dm->persist($teacher);
        $address = new Address('street1');
        $address->setTeacher($teacher);
        $this->dm->persist($address);
        $this->dm->flush();

        // Act
        $this->dm->detachAll();
        $foundTeacher = $this->dm->find(Teacher::class, $teacher->getId());

        // Assert
        $this->assertEquals($teacher->getId(), $foundTeacher->getId());
        $this->assertNotNull($foundTeacher->address());
        $this->assertEquals($address->getId(), $foundTeacher->address()->getId());
    }

    public function testHasOneWhenFindingByIdAttachedEntitiesWithExisitingChildEntity()
    {
        // Arrange
        $this->setUpAssociations();
        $teacher = new Teacher('Tom');
        $this->dm->persist($teacher);
        $address = new Address('street1');
        $teacher->setAddress($address);
        $this->dm->persist($address);
        $this->dm->flush();

        // Act
        $foundTeacher = $this->dm->find(Teacher::class, $teacher->getId());

        // Assert
        $this->assertEquals($teacher->getId(), $foundTeacher->getId());
        $this->assertNotNull($foundTeacher->address());
        $this->assertEquals($address->getId(), $foundTeacher->address()->getId());
    }

    /*
     * BELONGS TO INSERTS
     */

    /**
     * @expectedException Exception
     */
    public function testBelongsToWhenInsertingWithUnkownOwningEntity()
    {
        // Arrange
        $this->setUpAssociations();
        $teacher = new Teacher('Tom');
        $student = new Student('Jenn', $teacher);

        // Act
        $this->dm->persist($student);
        $this->dm->flush();
    }

    public function testBelongsToWhenInsertingWithNewOwningEntity()
    {
        // Arrange
        $this->setUpAssociations();
        $teacher = new Teacher('Tom');
        $student = new Student('Jenn', $teacher);

        // Act
        $this->dm->persist($teacher);
        $this->dm->persist($student);
        $this->dm->flush();
        $this->dm->detachAll();
        $teacherData = $this->dm->queryBuilder()->table('teacher')->where('id', '=', $teacher->getId())->select()[0];
        $studentData = $this->dm->queryBuilder()->table('student')->where('id', '=', $student->getId())->select()[0];

        // Assert
        $this->assertEquals($teacher->getId(), $teacherData['id']);
        $this->assertEquals($student->getId(), $studentData['id']);
        $this->assertEquals($teacher->getId(), $studentData['teacher_id']);
    }

    public function testBelongsToWhenInsertingWithManagedOwningEntity()
    {
        // Arrange
        $this->setUpAssociations();
        $teacher = new Teacher('Tom');
        $this->dm->persist($teacher);
        $this->dm->flush();

        // Act
        $student = new Student('Jenn', $teacher);
        $this->dm->persist($student);
        $this->dm->flush();
        $this->dm->detachAll();
        $teacherData = $this->dm->queryBuilder()->table('teacher')->where('id', '=', $teacher->getId())->select()[0];
        $studentData = $this->dm->queryBuilder()->table('student')->where('id', '=', $student->getId())->select()[0];

        // Assert
        $this->assertEquals($teacher->getId(), $teacherData['id']);
        $this->assertEquals($student->getId(), $studentData['id']);
        $this->assertEquals($teacher->getId(), $studentData['teacher_id']);
    }

    /**
     * @expectedException Exception
     */
    public function testBelongsToWhenInsertingWithNullOwningEntityIfNonNullable()
    {
        // Arrange
        $this->setUpAssociations();

        // Act
        $student = new Student('Jenn', null);
        $this->dm->persist($student);
        $this->dm->flush();
    }

    /*
     * BELONGS TO UPDATES
     */

    public function testBelongsToWhenUpdatingFromExistingToNew()
    {
        // Arrange
        $this->setUpAssociations();
        $teacher = new Teacher('Tom');
        $this->dm->persist($teacher);
        $student = new Student('Jenn', $teacher);
        $this->dm->persist($student);
        $this->dm->flush();

        // Act
        $teacher2 = new Teacher('Brad');
        $this->dm->persist($teacher2);
        $student->setTeacher($teacher2);
        $this->dm->flush();
        $this->dm->detachAll();
        $teacherData = $this->dm->queryBuilder()->table('teacher')->where('id', '=', $teacher->getId())->select()[0];
        $teacher2Data = $this->dm->queryBuilder()->table('teacher')->where('id', '=', $teacher2->getId())->select()[0];
        $studentData = $this->dm->queryBuilder()->table('student')->where('id', '=', $student->getId())->select()[0];

        // Assert
        $this->assertEquals($teacher->getId(), $teacherData['id']);
        $this->assertEquals($teacher2->getId(), $teacher2Data['id']);
        $this->assertEquals($student->getId(), $studentData['id']);
        $this->assertEquals($teacher2->getId(), $studentData['teacher_id']);
    }

    public function testBelongsToWhenUpdatingFromExistingToManaged()
    {
        // Arrange
        $this->setUpAssociations();
        $teacher = new Teacher('Tom');
        $this->dm->persist($teacher);
        $student = new Student('Jenn', $teacher);
        $this->dm->persist($student);
        $this->dm->flush();

        // Act
        $teacher2 = new Teacher('Brad');
        $this->dm->persist($teacher2);
        $this->dm->flush();
        $student->setTeacher($teacher2);
        $this->dm->flush();
        $this->dm->detachAll();
        $teacherData = $this->dm->queryBuilder()->table('teacher')->where('id', '=', $teacher->getId())->select()[0];
        $teacher2Data = $this->dm->queryBuilder()->table('teacher')->where('id', '=', $teacher2->getId())->select()[0];
        $studentData = $this->dm->queryBuilder()->table('student')->where('id', '=', $student->getId())->select()[0];

        // Assert
        $this->assertEquals($teacher->getId(), $teacherData['id']);
        $this->assertEquals($teacher2->getId(), $teacher2Data['id']);
        $this->assertEquals($student->getId(), $studentData['id']);
        $this->assertEquals($teacher2->getId(), $studentData['teacher_id']);
    }

    /**
     * @expectedException Exception
     */
    public function testBelongsToWhenUpdatingFromExistingToNullForNonNullable()
    {
        // Arrange
        $this->setUpAssociations();
        $teacher = new Teacher('Tom');
        $this->dm->persist($teacher);
        $student = new Student('Jenn', $teacher);
        $this->dm->persist($student);
        $this->dm->flush();

        // Act
        $student->setTeacher(null);
        $this->dm->flush();
    }

    /*
     * BELONGS TO REMOVALS
     */

    public function testBelongsToWhenRemoving()
    {
        // Arrange
        $this->setUpAssociations();
        $teacher = new Teacher('Tom');
        $this->dm->persist($teacher);
        $student = new Student('Jenn', $teacher);
        $this->dm->persist($student);
        $this->dm->flush();

        // Act
        $this->dm->delete($student);
        $this->dm->flush();
        $this->dm->detachAll();
        $teacherData = $this->dm->queryBuilder()->table('teacher')->where('id', '=', $teacher->getId())->select()[0];
        $studentData = $this->dm->queryBuilder()->table('student')->where('id', '=', $student->getId())->select();

        // Assert
        $this->assertEquals($teacher->getId(), $teacherData['id']);
        $this->assertEquals(0, sizeof($studentData));
    }

    /*
     * BELONGS TO FIND
     */

    public function testBelongsToWhenFindingByIdWithExistingUnatachedOwner()
    {
        // Arrange
        $this->setUpAssociations();
        $teacher = new Teacher('Tom');
        $this->dm->persist($teacher);
        $student = new Student('Jenn', $teacher);
        $this->dm->persist($student);
        $this->dm->flush();

        // Act
        $this->dm->detachAll();
        $foundStudent = $this->dm->find(Student::class, $student->getId());

        // Assert
        $this->assertEquals($student->getId(), $foundStudent->getId());
        $this->assertNotNull($student->teacher());
        $this->assertEquals($teacher->getId(), $student->teacher()->getId());
    }

    public function testBelongsToWhenFindingByIdWithExistingManagedOwner()
    {
        // Arrange
        $this->setUpAssociations();
        $teacher = new Teacher('Tom');
        $this->dm->persist($teacher);
        $student = new Student('Jenn', $teacher);
        $this->dm->persist($student);
        $this->dm->flush();

        // Act
        $this->dm->detach($student);
        $foundStudent = $this->dm->find(Student::class, $student->getId());

        // Assert
        $this->assertEquals($student->getId(), $foundStudent->getId());
        $this->assertNotNull($student->teacher());
        $this->assertEquals($teacher->getId(), $student->teacher()->getId());
    }

    public function testBelongsToWhenFindingByIdWithExistingManagedOwnerAndEntity()
    {
        // Arrange
        $this->setUpAssociations();
        $teacher = new Teacher('Tom');
        $this->dm->persist($teacher);
        $student = new Student('Jenn', $teacher);
        $this->dm->persist($student);
        $this->dm->flush();

        // Act
        $foundStudent = $this->dm->find(Student::class, $student->getId());

        // Assert
        $this->assertEquals($student->getId(), $foundStudent->getId());
        $this->assertNotNull($student->teacher());
        $this->assertEquals($teacher->getId(), $student->teacher()->getId());
    }

    /*
     * HAS MANY INSERTS
     */

    public function testHasManyWhenInsertingThatTheEmptyEntityCollectionIsChangedToPersistent()
    {
        // Arrange
        $this->setUpAssociations();
        $teacher = new Teacher('Tom');

        // Act
        $this->dm->persist($teacher);
        $this->dm->flush();

        // Assert
        $this->assertTrue($teacher->students() instanceof PersistentCollection);
    }

    public function testHasManyWhenInsertingThatTheNonEmptyEntityCollectionIsChangedToPersistent()
    {
        // Arrange
        $this->setUpAssociations();
        $teacher = new Teacher('Tom');
        $this->dm->persist($teacher);
        for ($i = 0; $i < 10; $i++)
        {
            $student = new Student('student'.$i, $teacher);
            $this->dm->persist($student);
            $teacher->addStudent($student);
        }

        // Act
        $this->dm->flush();

        // Assert
        $this->assertTrue($teacher->students() instanceof PersistentCollection);
        $this->assertEquals(10, $teacher->students()->count());
    }

    public function testHasManyWhenInsertingWithNewCollectionItems()
    {
        // Arrange
        $this->setUpAssociations();
        $teacher = new Teacher('Tom');
        $this->dm->persist($teacher);
        for ($i = 0; $i < 10; $i++)
        {
            $student = new Student('student'.$i, $teacher);
            $this->dm->persist($student);
            $teacher->addStudent($student);
        }

        // Act
        $this->dm->flush();
        $allStudentData = $this->dm->queryBuilder()->table('student')->select();

        // Assert
        $this->assertTrue($teacher->students() instanceof PersistentCollection);
        $this->assertEquals(10, $teacher->students()->count());
        foreach ($allStudentData as $studentData)
        {
            $this->assertEquals($teacher->getId(), $studentData['teacher_id']);
        }
    }

    /*
     * HAS MANY FINDS
     */

    public function testHasManyWhenFindingUnattachedEntityWithUnattachedItems()
    {
        // Arrange
        $this->setUpAssociations();
        $teacher = new Teacher('Tom');
        $this->dm->persist($teacher);
        for ($i = 0; $i < 10; $i++)
        {
            $student = new Student('student'.$i, $teacher);
            $this->dm->persist($student);
            $teacher->addStudent($student);
        }
        $this->dm->flush();

        // Act
        $this->dm->detachAll();
        $foundTeacher = $this->dm->find(Teacher::class, $teacher->getId());

        // Assert
        $this->assertTrue($foundTeacher->students() instanceof PersistentCollection);
        $this->assertEquals(10, $foundTeacher->students()->count());
    }

    public function testHasManyWhenFindingAttachedEntityAndAttachedItems()
    {
        // Arrange
        $this->setUpAssociations();
        $teacher = new Teacher('Tom');
        $this->dm->persist($teacher);
        for ($i = 0; $i < 10; $i++)
        {
            $student = new Student('student'.$i, $teacher);
            $this->dm->persist($student);
            $teacher->addStudent($student);
        }
        $this->dm->flush();

        // Act
        $foundTeacher = $this->dm->find(Teacher::class, $teacher->getId());

        // Assert
        $this->assertTrue($foundTeacher->students() instanceof PersistentCollection);
        $this->assertEquals(10, $foundTeacher->students()->count());
    }

    /*
     * HAS MANY UPDATES
     */

    public function testHasManyWhenUpdatingAfterAddingNewEntities()
    {
        // Arrange
        $this->setUpAssociations();
        $teacher = new Teacher('Tom');
        $this->dm->persist($teacher);
        $student0 = new Student('student0', $teacher);
        $this->dm->persist($student0);
        $teacher->addStudent($student0);
        for ($i = 1; $i < 10; $i++)
        {
            $student = new Student('student'.$i, $teacher);
            $this->dm->persist($student);
            $teacher->addStudent($student);
        }
        $this->dm->flush();

        // Act
        $extraS1 = new Student('extra1', $teacher);
        $extraS2 = new Student('extra2', $teacher);
        $this->dm->persist($extraS1);
        $this->dm->persist($extraS2);
        $teacher->addStudent($extraS1);
        $teacher->addStudent($extraS2);
        $this->dm->flush();
        $student0Data = $this->dm->queryBuilder()->table('student')->where('id', '=', $student0->getId())->select()[0];
        $extraS1Data = $this->dm->queryBuilder()->table('student')->where('id', '=', $extraS1->getId())->select()[0];

        // Assert
        $this->assertTrue($teacher->students() instanceof PersistentCollection);
        $this->assertEquals(12, $teacher->students()->count());
        $this->assertEquals($teacher->getId(), $student0Data['teacher_id']);
        $this->assertEquals($teacher->getId(), $extraS1Data['teacher_id']);
    }

    public function testHasManyWhenUpdatingAfterAddingManagedEntities()
    {
        // Arrange
        $this->setUpAssociations();
        $teacher = new Teacher('Tom');
        $this->dm->persist($teacher);
        $student0 = new Student('student0', $teacher);
        $this->dm->persist($student0);
        $teacher->addStudent($student0);
        for ($i = 1; $i < 10; $i++)
        {
            $student = new Student('student'.$i, $teacher);
            $this->dm->persist($student);
            $teacher->addStudent($student);
        }
        $this->dm->flush();

        // Act
        $extraS1 = new Student('extra1', $teacher);
        $extraS2 = new Student('extra2', $teacher);
        $this->dm->persist($extraS1);
        $this->dm->persist($extraS2);
        $this->dm->flush();
        $teacher->addStudent($extraS1);
        $teacher->addStudent($extraS2);
        $this->dm->flush();
        $student0Data = $this->dm->queryBuilder()->table('student')->where('id', '=', $student0->getId())->select()[0];
        $extraS1Data = $this->dm->queryBuilder()->table('student')->where('id', '=', $extraS1->getId())->select()[0];

        // Assert
        $this->assertTrue($teacher->students() instanceof PersistentCollection);
        $this->assertEquals(12, $teacher->students()->count());
        $this->assertEquals($teacher->getId(), $student0Data['teacher_id']);
        $this->assertEquals($teacher->getId(), $extraS1Data['teacher_id']);
    }

    /*
     * HAS MANY DELETES
     */

    public function testHasManyDeletingWithCascadeOnStudents()
    {
        // Arrange
        $this->setUpAssociations();
        $teacher = new Teacher('Tom');
        $this->dm->persist($teacher);
        for ($i = 1; $i < 10; $i++)
        {
            $student = new Student('student'.$i, $teacher);
            $this->dm->persist($student);
            $teacher->addStudent($student);
        }
        $this->dm->flush();

        // Act
        $this->dm->delete($teacher);
        $this->dm->flush();
        $studentData = $this->dm->queryBuilder()->table('student')->where('teacher_id', '=', $teacher->getId())->select();
        $teacherData = $this->dm->queryBuilder()->table('teacher')->where('id', '=', $teacher->getId())->select();

        // Assert
        $this->assertEquals(0, sizeof($studentData));
        $this->assertEquals(0, sizeof($teacherData));
    }

    /* MANY TO MANY FINDS */

    public function testManyToManyFind()
    {
        // Arrange
        $this->setUpAssociations();
        $teacher = new Teacher('Tom');
        $this->dm->persist($teacher);
        $student = new Student('student1', $teacher);
        $this->dm->persist($student);
        $teacher->addStudent($student);
        $event = new Event('event1');
        $this->dm->persist($event);
        $lesson = new Lesson('lesson1', $student, $event);
        $this->dm->persist($lesson);
        $event->addLesson($lesson);
        $student->addLesson($lesson);
        $this->dm->flush();
        $studentId = $student->getId();

        // Act
        $this->dm->detachAll();
        $student = $this->dm->find(Student::class, $studentId);

        // Assert
        $this->assertNotNull($student);
        $this->assertEquals(1, $student->lessons()->count());
        $this->assertEquals('lesson1', $student->lessons()->first()->name());
        $this->assertEquals('event1', $student->lessons()->first()->event()->name());
    }
}