<?php

require_once 'config.php';

/** @var ExpertSystem\Resource\Manager $manager */

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Encoder\JsonEncoder;

$app->before(function (Request $request) {
    if (
        $request->getContent() &&
        strpos($request->headers->get('Content-Type'), 'application/json') === 0
    ) {
            $data = (new JsonEncoder())->decode($request->getContent(), JsonEncoder::FORMAT, [
                'json_decode_associative' => true
            ]);

            $request->request->replace(is_array($data) ? $data : []);
    }
});

$app->get('/', function() use ($app, $manager) {
    try {
        $nodeValues = array_map(function ($node) {
            return $node->getValue();
        }, $manager->getAllNodes(['value' => 'DESC']));

        return $app['response_handle']($nodeValues);
    } catch (Exception $ex) {
        return $app['exception_handle']($ex);
    }
});

$app->get('/{value}', function($value) use ($app, $manager) {
    try {
        $childValues = [];
        $value = urldecode($value);

        if ($value == 'root') {
            $node = $manager->getRootNode();

            if (!$node) {
                return $app['response_handle']([]);
            }

            foreach ($node->getChildNodes() as $childNodeRelationship) {
                $childValues[] = [
                    'relationship_value' => $childNodeRelationship->getValue(),
                    'statement_value' => $childNodeRelationship->getChildNode()->getValue()
                ];
            }

            return $app['response_handle']([
                'child_statements' => $childValues,
                'statement_value' => $node->getValue(),
                'parent_relationship_value' => null,
                'parent_statement_value' => null
            ]);
        }

        $relationship = $manager->getRelationship($value);
        $childNode = $relationship->getChildNode();
        $parentNode = $relationship->getParentNode();

        foreach ($childNode->getChildNodes() as $childNodeRelationship) {
            $childValues[] = [
                'relationship_value' => $childNodeRelationship->getValue(),
                'statement_value' => $childNodeRelationship->getChildNode()->getValue()
            ];
        }

        return $app['response_handle']([
            'child_statements' => $childValues,
            'statement_value' => $childNode->getValue(),
            'parent_relationship_value' => $relationship->getValue(),
            'parent_statement_value' => $parentNode ? $parentNode->getValue() : null
        ]);
    } catch (Exception $ex) {
        return $app['exception_handle']($ex);
    }
});

$app->put('/', function(Request $request) use ($app, $manager) {
    try {
        $statementValue = $request->request->get('statement_value');
        $newStatementValue = $request->request->get('new_statement_value');
        $newParentStatementValue = $request->request->get('new_parent_statement_value');
        $newParentRelationshipValue = $request->request->get('new_parent_relationship_value');

        if ($newParentStatementValue && $statementValue != $newParentStatementValue) {
            $manager->updateNodeLink(
                $statementValue,
                $newParentStatementValue,
                $newParentRelationshipValue
            );
        }

        if ($statementValue != $newStatementValue) {
            $manager->updateNodeValue($statementValue, $newStatementValue);
        }

        return $app['response_handle'](['success' => 'Statement removed successfully']);
    } catch (Exception $ex) {
        return $app['exception_handle']($ex);
    }
});

$app->delete('/', function(Request $request) use ($app, $manager) {
    try {
        $manager->removeNode(
            $request->request->get('statement_value'),
            $request->request->get('with_children')
        );

        return $app['response_handle'](['success' => 'Statement removed successfully']);
    } catch (Exception $ex) {
        return $app['exception_handle']($ex);
    }
});

$app->post('/', function(Request $request) use ($app, $manager) {
    try {
        $manager->createNode(
            $request->request->get('statement_value'),
            $request->request->get('parent_statement_value'),
            $request->request->get('parent_relationship_value')
        );

        return $app['response_handle'](['success' => 'Statement created successfully']);
    } catch (Exception $ex) {
        return $app['exception_handle']($ex);
    }
});

$app->post('/import', function(Request $request) use ($app, $manager) {
    try {
        $manager->import(
            urldecode($request->request->get('url')),
            $request->request->get('options')
        );

        return $app['response_handle'](['success' => 'Import has done successfully']);
    } catch (Exception $ex) {
        return $app['exception_handle']($ex);
    }
});

$app->run();
