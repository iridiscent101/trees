<?php

namespace App\Enums;

enum PhotoType: string
{
    case Main = 'main';
    case Tree = 'tree';
    case Leaf = 'leaf';
    case Bark = 'bark';
    case Fruit = 'fruit';
    case Monitoring = 'monitoring';
    case Other = 'other';
}
