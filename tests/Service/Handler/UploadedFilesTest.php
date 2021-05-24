<?php

namespace App\Tests\Service\Handler;

use App\Entity\Projects;
use App\Entity\User;
use App\Service\Extractor\ClassName;
use App\Service\Generator\Path;
use App\Service\Handler\UploadedFiles;
use Countable;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class UploadedFilesTest extends TestCase
{
  /**
   * Undocumented function
   *
   * @param [] $structure
   * @param string|null $description
   * @param string|null $previewPicture
   * @param int $userId
   * @param int $projectId
   * @param [] $expectedStructure
   * @param string $expectedDescription
   * @param string $expectedPreviewPicture
   * 
   * @return void
   * 
   * @dataProvider uploadedFilesDataProvider
   */
  public function testUploadedFiles($structure, $description, $previewPicture, $userId, $projectId, $expectedStructure, $expectedDescription, $expectedPreviewPicture)
  {
    $fileSystem = vfsStream::setup('root', null, $structure);

    $projects = new Projects();
    $projects->setId($projectId);
    $projects->setDescription($description);
    $projects->setPreviewPicture($previewPicture);

    /** @var MockObject|User */
    $user = $this->createMock(User::class);
    $user->method('getId')->willReturn($userId);

    $classNameExtractor = new ClassName();
    $pathGenerator = new Path($fileSystem->url(), $classNameExtractor);
    $uploadedFiles = new UploadedFiles($user, $pathGenerator);

    $uploadedFiles->handle($projects);

    $this->assertSame($expectedDescription, $projects->getDescription());
    $this->assertSame($expectedPreviewPicture, $projects->getPreviewPicture());

    $this->assertStructureEquals($expectedStructure, $fileSystem);
  }

  public function uploadedFilesDataProvider()
  {
    yield 'test empty description and empty preview picture' => [
      'structure' => [
        'public' => [
          'upload' => [
            1 => [
              'user_1_new_file_2.jpg' => 'user_1_new_file_2.jpg_content',
              'user_1_new_file_3.jpg' => 'user_1_new_file_3.jpg_content',
            ],
            2 => [
              'user_2_new_file_1.jpg' => 'user_2_new_file_1.jpg_content',
              'user_2_new_file_4.jpg' => 'user_2_new_file_4.jpg_content',
              'user_2_new_file_12.jpg' => 'user_2_new_file_12.jpg_content',
              'user_2_new_file_51.jpg' => 'user_2_new_file_51.jpg_content',
            ]
          ],
          'images' => [
            'content' => [
              'dynamisch' => [
                'projects' => [
                  10 => [
                    'project_10_existing_file_1.jpg' => 'project_10_existing_file_1.jpg_content',
                    'project_10_existing_file_10.png' => 'project_10_existing_file_10.png_content'
                  ]
                ]
              ],
              'statisch' => [
              ]
            ]
          ]
        ]
      ],
      'description' => '',
      'previewPicture' => null,
      'userId' => 10,
      'projectId' => 12,
      'expectedStructure' => [
        'public' => [
          'upload' => [
            1 => [
              'user_1_new_file_2.jpg' => 'user_1_new_file_2.jpg_content',
              'user_1_new_file_3.jpg' => 'user_1_new_file_3.jpg_content',
            ],
            2 => [
              'user_2_new_file_1.jpg' => 'user_2_new_file_1.jpg_content',
              'user_2_new_file_4.jpg' => 'user_2_new_file_4.jpg_content',
              'user_2_new_file_12.jpg' => 'user_2_new_file_12.jpg_content',
              'user_2_new_file_51.jpg' => 'user_2_new_file_51.jpg_content',
            ]
          ],
          'images' => [
            'content' => [
              'dynamisch' => [
                'projects' => [
                  10 => [
                    'project_10_existing_file_1.jpg' => 'project_10_existing_file_1.jpg_content',
                    'project_10_existing_file_10.png' => 'project_10_existing_file_10.png_content'
                  ]
                ]
              ],
              'statisch' => [
              ]
            ]
          ]
        ]
      ],
      'expectedDescription' => '',
      'expectedPreviewPicture' => null
    ];

    yield 'test empty description and set preview picture' => [
      'structure' => [
        'public' => [
          'upload' => [
            1 => [
              'user_1_new_file_2.jpg' => 'user_1_new_file_2.jpg_content',
              'user_1_new_file_3.jpg' => 'user_1_new_file_3.jpg_content',
            ],
            2 => [
              'user_2_new_file_1.jpg' => 'user_2_new_file_1.jpg_content',
              'user_2_new_file_4.jpg' => 'user_2_new_file_4.jpg_content',
              'user_2_new_file_12.jpg' => 'user_2_new_file_12.jpg_content',
              'user_2_new_file_51.jpg' => 'user_2_new_file_51.jpg_content',
            ],
            10 => [
              'preview_picture.png' => 'user_10_preview_picture_content'
            ]
          ],
          'images' => [
            'content' => [
              'dynamisch' => [
                'projects' => [
                  10 => [
                    'project_10_existing_file_1.jpg' => 'project_10_existing_file_1.jpg_content',
                    'project_10_existing_file_10.png' => 'project_10_existing_file_10.png_content'
                  ]
                ]
              ],
              'statisch' => [
              ]
            ]
          ]
        ]
      ],
      'description' => '',
      'previewPicture' => '/upload/10/preview_picture.png',
      'userId' => 10,
      'projectId' => 12,
      'expectedStructure' => [
        'public' => [
          'upload' => [
            1 => [
              'user_1_new_file_2.jpg' => 'user_1_new_file_2.jpg_content',
              'user_1_new_file_3.jpg' => 'user_1_new_file_3.jpg_content',
            ],
            2 => [
              'user_2_new_file_1.jpg' => 'user_2_new_file_1.jpg_content',
              'user_2_new_file_4.jpg' => 'user_2_new_file_4.jpg_content',
              'user_2_new_file_12.jpg' => 'user_2_new_file_12.jpg_content',
              'user_2_new_file_51.jpg' => 'user_2_new_file_51.jpg_content',
            ],
            10 => [
            ]
          ],
          'images' => [
            'content' => [
              'dynamisch' => [
                'projects' => [
                  10 => [
                    'project_10_existing_file_1.jpg' => 'project_10_existing_file_1.jpg_content',
                    'project_10_existing_file_10.png' => 'project_10_existing_file_10.png_content'
                  ],
                  12 => [
                    'preview_picture.png' => 'user_10_preview_picture_content'
                  ]
                ]
              ],
              'statisch' => [
              ]
            ]
          ]
        ]
      ],
      'expectedDescription' => '',
      'expectedPreviewPicture' => '/images/content/dynamisch/projects/12/preview_picture.png'
    ];

    yield 'test description with all existing files, one to many file and set preview picture' => [
      'structure' => [
        'public' => [
          'upload' => [
            1 => [
              'user_1_new_file_2.jpg' => 'user_1_new_file_2.jpg_content',
              'user_1_new_file_3.jpg' => 'user_1_new_file_3.jpg_content',
            ],
            2 => [
              'user_2_new_file_1.jpg' => 'user_2_new_file_1.jpg_content',
              'user_2_new_file_4.jpg' => 'user_2_new_file_4.jpg_content',
              'user_2_new_file_12.jpg' => 'user_2_new_file_12.jpg_content',
              'user_2_new_file_51.jpg' => 'user_2_new_file_51.jpg_content',
            ],
            10 => [
              'preview_picture.png' => 'user_10_preview_picture_content',
              'user_10_new_file_1.jpg' => 'user_10_new_file_1.jpg_content',
              'user 10 new file 4.jpg' => 'user 10 new file 4.jpg content',
              'user_10_new_file_12.jpg' => 'user_10_new_file_12.jpg_content',
              'user_10_new_file_51.jpg' => 'user_10_new_file_51.jpg_content',
            ]
          ],
          'images' => [
            'content' => [
              'dynamisch' => [
                'projects' => [
                  10 => [
                    'project_10_existing_file_1.jpg' => 'project_10_existing_file_1.jpg_content',
                    'project_10_existing_file_10.png' => 'project_10_existing_file_10.png_content'
                  ]
                ]
              ],
              'statisch' => [
              ]
            ]
          ]
        ]
      ],
      'description' => 'lorem ipsum trallala <div src="asasasa"><img src="/upload/10/user_10_new_file_1.jpg" /></div>'.
        '<img src="/upload/10/user 10 new file 4.jpg" alt="test alternate text" title="test title file 4" />asdsad asda dasd '.
        'asdsadas dasd asd ad s<img class="testclass testclass1" id="test_id" src="/upload/10/user_10_new_file_51.jpg" />',
      'previewPicture' => '/upload/10/preview_picture.png',
      'userId' => 10,
      'projectId' => 12,
      'expectedStructure' => [
        'public' => [
          'upload' => [
            1 => [
              'user_1_new_file_2.jpg' => 'user_1_new_file_2.jpg_content',
              'user_1_new_file_3.jpg' => 'user_1_new_file_3.jpg_content',
            ],
            2 => [
              'user_2_new_file_1.jpg' => 'user_2_new_file_1.jpg_content',
              'user_2_new_file_4.jpg' => 'user_2_new_file_4.jpg_content',
              'user_2_new_file_12.jpg' => 'user_2_new_file_12.jpg_content',
              'user_2_new_file_51.jpg' => 'user_2_new_file_51.jpg_content',
            ],
            10 => [
              'user_10_new_file_12.jpg' => 'user_10_new_file_12.jpg_content'
            ]
          ],
          'images' => [
            'content' => [
              'dynamisch' => [
                'projects' => [
                  10 => [
                    'project_10_existing_file_1.jpg' => 'project_10_existing_file_1.jpg_content',
                    'project_10_existing_file_10.png' => 'project_10_existing_file_10.png_content'
                  ],
                  12 => [
                    'preview_picture.png' => 'user_10_preview_picture_content',
                    'user_10_new_file_1.jpg' => 'user_10_new_file_1.jpg_content',
                    'user 10 new file 4.jpg' => 'user 10 new file 4.jpg content',
                    'user_10_new_file_51.jpg' => 'user_10_new_file_51.jpg_content',
                  ]
                ]
              ],
              'statisch' => [
              ]
            ]
          ]
        ]
      ],
      'expectedDescription' => 'lorem ipsum trallala <div src="asasasa"><img src="/images/content/dynamisch/projects/12/user_10_new_file_1.jpg" /></div>'.
        '<img src="/images/content/dynamisch/projects/12/user 10 new file 4.jpg" alt="test alternate text" title="test title file 4" />asdsad asda dasd '.
        'asdsadas dasd asd ad s<img class="testclass testclass1" id="test_id" src="/images/content/dynamisch/projects/12/user_10_new_file_51.jpg" />',
      'expectedPreviewPicture' => '/images/content/dynamisch/projects/12/preview_picture.png'
    ];

    yield 'test description with all existing files, many occurrences of the same file and set preview picture' => [
      'structure' => [
        'public' => [
          'upload' => [
            1 => [
              'user_1_new_file_2.jpg' => 'user_1_new_file_2.jpg_content',
              'user_1_new_file_3.jpg' => 'user_1_new_file_3.jpg_content',
            ],
            2 => [
              'user_2_new_file_1.jpg' => 'user_2_new_file_1.jpg_content',
              'user_2_new_file_4.jpg' => 'user_2_new_file_4.jpg_content',
              'user_2_new_file_12.jpg' => 'user_2_new_file_12.jpg_content',
              'user_2_new_file_51.jpg' => 'user_2_new_file_51.jpg_content',
            ],
            10 => [
              'preview_picture.png' => 'user_10_preview_picture_content',
              'user_10_new_file_1.jpg' => 'user_10_new_file_1.jpg_content',
              'user 10 new file 4.jpg' => 'user 10 new file 4.jpg content',
              'user_10_new_file_12.jpg' => 'user_10_new_file_12.jpg_content',
              'user_10_new_file_51.jpg' => 'user_10_new_file_51.jpg_content',
            ]
          ],
          'images' => [
            'content' => [
              'dynamisch' => [
                'projects' => [
                  10 => [
                    'project_10_existing_file_1.jpg' => 'project_10_existing_file_1.jpg_content',
                    'project_10_existing_file_10.png' => 'project_10_existing_file_10.png_content'
                  ]
                ]
              ],
              'statisch' => [
              ]
            ]
          ]
        ]
      ],
      'description' => 'lorem ipsum trallala <div src="asasasa"><img src="/upload/10/user_10_new_file_51.jpg" /></div>'.
        '<img src="/upload/10/user 10 new file 4.jpg" alt="test alternate text" title="test title file 4" />asdsad asda dasd '.
        'asdsadas dasd asd ad s<img class="testclass testclass1" id="test_id" src="/upload/10/user_10_new_file_51.jpg" />'.
        'asdsad asd asd<p>asdsadsad asdas dsa d</p><h1>asddasdas</h1><img id="test_id" src="/upload/10/user_10_new_file_51.jpg" />',
      'previewPicture' => '/upload/10/preview_picture.png',
      'userId' => 10,
      'projectId' => 12,
      'expectedStructure' => [
        'public' => [
          'upload' => [
            1 => [
              'user_1_new_file_2.jpg' => 'user_1_new_file_2.jpg_content',
              'user_1_new_file_3.jpg' => 'user_1_new_file_3.jpg_content',
            ],
            2 => [
              'user_2_new_file_1.jpg' => 'user_2_new_file_1.jpg_content',
              'user_2_new_file_4.jpg' => 'user_2_new_file_4.jpg_content',
              'user_2_new_file_12.jpg' => 'user_2_new_file_12.jpg_content',
              'user_2_new_file_51.jpg' => 'user_2_new_file_51.jpg_content',
            ],
            10 => [
              'user_10_new_file_1.jpg' => 'user_10_new_file_1.jpg_content',
              'user_10_new_file_12.jpg' => 'user_10_new_file_12.jpg_content'
            ]
          ],
          'images' => [
            'content' => [
              'dynamisch' => [
                'projects' => [
                  10 => [
                    'project_10_existing_file_1.jpg' => 'project_10_existing_file_1.jpg_content',
                    'project_10_existing_file_10.png' => 'project_10_existing_file_10.png_content'
                  ],
                  12 => [
                    'preview_picture.png' => 'user_10_preview_picture_content',
                    'user 10 new file 4.jpg' => 'user 10 new file 4.jpg content',
                    'user_10_new_file_51.jpg' => 'user_10_new_file_51.jpg_content',
                  ]
                ]
              ],
              'statisch' => [
              ]
            ]
          ]
        ]
      ],
      'expectedDescription' => 'lorem ipsum trallala <div src="asasasa"><img src="/images/content/dynamisch/projects/12/user_10_new_file_51.jpg" /></div>'.
        '<img src="/images/content/dynamisch/projects/12/user 10 new file 4.jpg" alt="test alternate text" title="test title file 4" />asdsad asda dasd '.
        'asdsadas dasd asd ad s<img class="testclass testclass1" id="test_id" src="/images/content/dynamisch/projects/12/user_10_new_file_51.jpg" />'.
        'asdsad asd asd<p>asdsadsad asdas dsa d</p><h1>asddasdas</h1><img id="test_id" src="/images/content/dynamisch/projects/12/user_10_new_file_51.jpg" />',
      'expectedPreviewPicture' => '/images/content/dynamisch/projects/12/preview_picture.png'
    ];

    yield 'test description with all existing files, one missing file and set preview picture' => [
      'structure' => [
        'public' => [
          'upload' => [
            1 => [
              'user_1_new_file_2.jpg' => 'user_1_new_file_2.jpg_content',
              'user_1_new_file_3.jpg' => 'user_1_new_file_3.jpg_content',
            ],
            2 => [
              'user_2_new_file_1.jpg' => 'user_2_new_file_1.jpg_content',
              'user_2_new_file_4.jpg' => 'user_2_new_file_4.jpg_content',
              'user_2_new_file_12.jpg' => 'user_2_new_file_12.jpg_content',
              'user_2_new_file_51.jpg' => 'user_2_new_file_51.jpg_content',
            ],
            10 => [
              'preview_picture.png' => 'user_10_preview_picture_content',
              'user_10_new_file_1.jpg' => 'user_10_new_file_1.jpg_content',
              'user_10_new_file_12.jpg' => 'user_10_new_file_12.jpg_content',
              'user_10_new_file_51.jpg' => 'user_10_new_file_51.jpg_content',
            ]
          ],
          'images' => [
            'content' => [
              'dynamisch' => [
                'projects' => [
                  10 => [
                    'project_10_existing_file_1.jpg' => 'project_10_existing_file_1.jpg_content',
                    'project_10_existing_file_10.png' => 'project_10_existing_file_10.png_content'
                  ]
                ]
              ],
              'statisch' => [
              ]
            ]
          ]
        ]
      ],
      'description' => 'lorem ipsum trallala <div src="asasasa"><img src="/upload/10/user_10_new_file_1.jpg" /></div>'.
        '<img src="/upload/10/user 10 new file 4.jpg" alt="test alternate text" title="test title file 4" />asdsad asda dasd '.
        'asdsadas dasd asd ad s<img class="testclass testclass1" id="test_id" src="/upload/10/user_10_new_file_51.jpg" />',
      'previewPicture' => '/upload/10/preview_picture.png',
      'userId' => 10,
      'projectId' => 12,
      'expectedStructure' => [
        'public' => [
          'upload' => [
            1 => [
              'user_1_new_file_2.jpg' => 'user_1_new_file_2.jpg_content',
              'user_1_new_file_3.jpg' => 'user_1_new_file_3.jpg_content',
            ],
            2 => [
              'user_2_new_file_1.jpg' => 'user_2_new_file_1.jpg_content',
              'user_2_new_file_4.jpg' => 'user_2_new_file_4.jpg_content',
              'user_2_new_file_12.jpg' => 'user_2_new_file_12.jpg_content',
              'user_2_new_file_51.jpg' => 'user_2_new_file_51.jpg_content',
            ],
            10 => [
              'user_10_new_file_12.jpg' => 'user_10_new_file_12.jpg_content'
            ]
          ],
          'images' => [
            'content' => [
              'dynamisch' => [
                'projects' => [
                  10 => [
                    'project_10_existing_file_1.jpg' => 'project_10_existing_file_1.jpg_content',
                    'project_10_existing_file_10.png' => 'project_10_existing_file_10.png_content'
                  ],
                  12 => [
                    'preview_picture.png' => 'user_10_preview_picture_content',
                    'user_10_new_file_1.jpg' => 'user_10_new_file_1.jpg_content',
                    'user_10_new_file_51.jpg' => 'user_10_new_file_51.jpg_content',
                  ]
                ]
              ],
              'statisch' => [
              ]
            ]
          ]
        ]
      ],
      'expectedDescription' => 'lorem ipsum trallala <div src="asasasa"><img src="/images/content/dynamisch/projects/12/user_10_new_file_1.jpg" /></div>'.
        '<img src="/upload/10/user 10 new file 4.jpg" alt="test alternate text" title="test title file 4" />asdsad asda dasd '.
        'asdsadas dasd asd ad s<img class="testclass testclass1" id="test_id" src="/images/content/dynamisch/projects/12/user_10_new_file_51.jpg" />',
      'expectedPreviewPicture' => '/images/content/dynamisch/projects/12/preview_picture.png'
    ];
  }

  /**
   * This function is not final because there are to many type tests.
   *
   * @param array $expectedStructure
   * @param [type] $fileSystem
   * @return void
   */
  private function assertStructureEquals(array $expectedStructure, $fileSystem)
  {
    if ($fileSystem instanceof Countable
      || is_array($fileSystem)
    ) {
      $this->assertSame(count($expectedStructure), count($fileSystem), "Expected: ".print_r($expectedStructure, true)." / Actual: ".print_r($fileSystem, true));
    } else {
      $this->assertSame(count($expectedStructure), count($fileSystem->getChildren()), "Expected: ".print_r($expectedStructure, true)." / Actual: ".print_r($fileSystem, true));
    }

    foreach ($expectedStructure as $name => $expectedPart) {
      if (is_array($expectedPart)) {
        if (is_array($fileSystem)) {
          $this->assertStructureEquals($expectedPart, $fileSystem[$name]);
        } else {
          $this->assertStructureEquals($expectedPart, $fileSystem->getChild((string)$name));
        }
      } else {
        $this->assertTrue($fileSystem->hasChild((string)$name), "Filesystem should have entry ".$name);
        $this->assertSame($expectedPart, file_get_contents($fileSystem->getChild((string)$name)->url()));
      }
    }
  }
}
