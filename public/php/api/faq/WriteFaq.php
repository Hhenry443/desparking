<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/php/models/Faq.php';

class WriteFaq extends Faq
{
    public function saveFaq(): int
    {
        $id       = isset($_POST['faq_id']) && ctype_digit($_POST['faq_id']) ? (int)$_POST['faq_id'] : null;
        $question = trim($_POST['question'] ?? '');
        $answer   = trim($_POST['answer'] ?? '');
        $category = in_array($_POST['category'] ?? '', ['general', 'owners', 'drivers']) ? $_POST['category'] : 'general';
        $sortOrder = isset($_POST['sort_order']) && ctype_digit($_POST['sort_order']) ? (int)$_POST['sort_order'] : 0;

        if (!$question) {
            http_response_code(400);
            exit('Question is required');
        }
        if (!$answer) {
            http_response_code(400);
            exit('Answer is required');
        }

        if ($id) {
            $this->updateFaq($id, $question, $answer, $category, $sortOrder);
            return $id;
        } else {
            return $this->insertFaq($question, $answer, $category, $sortOrder);
        }
    }
}
