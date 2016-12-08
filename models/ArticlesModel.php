<?php
/**
 * Created by PhpStorm.
 * User: n5ver
 * Date: 04.12.2016
 * Time: 11:22
 */

namespace Sp\Models;


class ArticlesModel extends Model
{
    public function getAllArticlesForHome() {
        $q = $this->db->prepare("SELECT * FROM prispevky WHERE schvaleno = 1");

        $q->execute();
        if($q->columnCount() > 0) {
            return $q->fetchAll();
        }

        else {
            return null;
        }
    }

    public function getAllArticles() {
        $q = $this->db->prepare("SELECT * FROM prispevky");

        $q->execute();

        return $q->fetchAll();
    }

    public function getMyArticles() {
        $q = $this->db->prepare("SELECT * FROM prispevky WHERE id_uzivatel = :id");
        $q->bindValue(":id", $_SESSION['uzivatel']['id']);
        $q->execute();

        if($q->columnCount() > 0) {
            return $q->fetchAll();
        }

        else {
            return null;
        }
    }

    private function uploadFile($pdf) {
        $file_name = $pdf['name'];
        $file_size = $pdf['size'];
        $file_tmp = $pdf['tmp_name'];
        $pdfname = $pdf['name'];
        $file_ext = strtolower(pathinfo($pdfname, PATHINFO_EXTENSION));

        $expensions = array("pdf");

        if(in_array($file_ext, $expensions) == false){
            return false;
        }

        if($file_size > 5242880){
            return false;
        }

        if(move_uploaded_file($file_tmp, ROOT . "www" . DIRECTORY_SEPARATOR . "pdf" . DIRECTORY_SEPARATOR . $file_name)) {
            return true;
        }

        else {
            return false;
        }
    }

    public function add_article($article, $id, $pdf) {
        $schvaleno = 0;
        $prumer = 0.0;
        $q = $this->db->prepare("INSERT INTO prispevky (nazev, autori, abstract, pdf, id_uzivatel, schvaleno, prumer_hodnoc) 
                                      VALUES (:nazev, :autori, :abstract, :pdf, :id_uzivatele, :schvaleno, :prumer_hodnoc)");
        $naz = $article['nazev'];
        $nazev = htmlspecialchars(stripslashes($naz), ENT_QUOTES, 'UTF-8');
        $aut = $article['autori'];
        $autori = htmlspecialchars(stripslashes($aut), ENT_QUOTES, 'UTF-8');
        $q->bindValue(":nazev", $nazev);
        $q->bindValue(":autori", $autori);
        $abstract = $article['abstract'];
        $q->bindValue(":abstract", htmlspecialchars(stripslashes($abstract), ENT_QUOTES, 'UTF-8'));
        $q->bindValue(":pdf", htmlspecialchars(stripslashes($pdf['name']), ENT_QUOTES, 'UTF-8'));
        $q->bindValue(":id_uzivatele", htmlspecialchars(stripslashes($id), ENT_QUOTES, 'UTF-8'));
        $q->bindValue(":schvaleno", $schvaleno);
        $q->bindValue(":prumer_hodnoc", $prumer);

        if($this->uploadFile($pdf)) {
            if(!$q->execute()) {
                return false;
            }

            else {
                return true;
            }
        }
    }

    public function getArticleById($id) {
        $q = $this->db->prepare("SELECT * FROM prispevky WHERE id = :id");
        $q->bindValue(":id", $id);
        $q->execute();

        if($q->columnCount() > 0) {
            return $q->fetch();
        }

        else {
            return null;
        }
    }

    public function setAllowOrDeny($allow, $id) {
        $q = $this->db->prepare("UPDATE prispevky SET schvaleno = :schvaleno WHERE id = :id");
        $q->bindValue(":schvaleno", $allow);
        $q->bindValue(":id", $id);
        $q->execute();
    }

    public function getPDF($id) {
        $q = $this->db->prepare("SELECT pdf FROM prispevky WHERE id = :id");
        $q->bindValue(":id", $id);
        $q->execute();

        return $q->fetch();
    }

    private function deletePDF($pdf) {
        $file = ROOT . "www" . DIRECTORY_SEPARATOR . "pdf" . DIRECTORY_SEPARATOR . $pdf['pdf'];

        $path = realpath($file);

        unlink($path);
    }

    public function deletArticle($id) {
        $q = $this->db->prepare("DELETE FROM prispevky WHERE id = :id");
        $q->bindValue(":id", $id);
        $pdf = $this->getPDF($id);
        $q->execute();

        $this->deletePDF($pdf);
    }

    public function updateArticle($article, $pdf = null) {
        if($pdf == null) {
            $newPdf = $article['pdf'];
        }

        else {
            $newPdf = $pdf;
            $this->deletePDF($article['pdf']);
            $this->uploadFile($newPdf);
        }

        $q = $this->db->prepare("UPDATE prispevky SET nazev = :nazev, autori = :autori, abstract = :abstract, pdf = :pdf 
                                  WHERE id = :id");
        $q->bindValue(":nazev", htmlspecialchars(stripslashes($article['nazev']), ENT_QUOTES, 'UTF-8'));
        $q->bindValue(":autori", htmlspecialchars(stripslashes($article['autori']), ENT_QUOTES, 'UTF-8'));
        $q->bindValue(":abstract", htmlspecialchars(stripslashes($article['abstract']), ENT_QUOTES, 'UTF-8'));
        $q->bindValue(":pdf", htmlspecialchars(stripslashes($newPdf['name']), ENT_QUOTES, 'UTF-8'));
        $q->bindValue(":id", htmlspecialchars(stripslashes($article['id']), ENT_QUOTES, 'UTF-8'));
        $q->execute();
    }

    public function updateArticleScore($id, $score) {
        $q = $this->db->prepare("UPDATE prispevky SET prumer_hodnoc = :score WHERE id = :id");

        $q->bindValue(":score", $score);
        $q->bindValue(":id", $id);
        $q->execute();
    }
}