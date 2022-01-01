<?php

namespace Form;

use Framelix\Framelix\Config;
use Framelix\Framelix\Form\Field\Bic;
use Framelix\Framelix\Form\Field\Captcha;
use Framelix\Framelix\Form\Field\Color;
use Framelix\Framelix\Form\Field\Date;
use Framelix\Framelix\Form\Field\DateTime;
use Framelix\Framelix\Form\Field\Email;
use Framelix\Framelix\Form\Field\File;
use Framelix\Framelix\Form\Field\Grid;
use Framelix\Framelix\Form\Field\Hidden;
use Framelix\Framelix\Form\Field\Html;
use Framelix\Framelix\Form\Field\Iban;
use Framelix\Framelix\Form\Field\Number;
use Framelix\Framelix\Form\Field\Password;
use Framelix\Framelix\Form\Field\Search;
use Framelix\Framelix\Form\Field\Select;
use Framelix\Framelix\Form\Field\Text;
use Framelix\Framelix\Form\Field\Textarea;
use Framelix\Framelix\Form\Field\Time;
use Framelix\Framelix\Form\Field\Toggle;
use Framelix\Framelix\Form\Field\TwoFactorCode;
use Framelix\Framelix\Form\Form;
use Framelix\Framelix\Utils\Buffer;
use Framelix\FramelixTests\TestCase;

final class FormTest extends TestCase
{
    public function tests(): void
    {
        $form = $this->getFormWithAllFields();

        $this->assertFalse(Form::isFormSubmitted($form->id));
        $this->setSimulatedGetData([$form->id => '1']);
        $this->assertTrue(Form::isFormSubmitted($form->id));
        $this->setSimulatedPostData([$form->id => '1']);
        $this->assertTrue(Form::isFormSubmitted($form->id));

        Buffer::start();
        $form->submitAsync = false;
        $form->show();
        $this->assertStringContainsString("FramelixForm.createFromPhpData", Buffer::get());
    }

    /**
     * @return Form
     */
    private function getFormWithAllFields(): Form
    {
        $form = new Form();
        $form->id = 'test';

        $field = new Bic();
        $field->name = $field::class;
        $form->addField($field);

        Config::set('captchaKeys[recaptchav2][publicKey]', 'test');
        Config::set('captchaKeys[recaptchav2][privateKey]', 'test');
        $field = new Captcha();
        $field->type = $field::TYPE_RECAPTCHA_V2;
        $field->name = $field::class;
        $form->addField($field);

        $field = new Color();
        $field->name = $field::class;
        $form->addField($field);

        $field = new Date();
        $field->name = $field::class;
        $form->addField($field);

        $field = new DateTime();
        $field->name = $field::class;
        $form->addField($field);

        $field = new Email();
        $field->name = $field::class;
        $form->addField($field);

        $field = new File();
        $field->name = $field::class;
        $form->addField($field);

        $field = new Grid();
        $field->name = $field::class;
        $form->addField($field);

        $field = new Hidden();
        $field->name = $field::class;
        $form->addField($field);

        $field = new Html();
        $field->name = $field::class;
        $form->addField($field);

        $field = new Iban();
        $field->name = $field::class;
        $form->addField($field);

        $field = new Number();
        $field->name = $field::class;
        $form->addField($field);

        $field = new Password();
        $field->name = $field::class;
        $form->addField($field);

        $field = new Search();
        $field->name = $field::class;
        $field->setSearchMethod(__CLASS__, 'test');
        $form->addField($field);

        $field = new Select();
        $field->name = $field::class;
        $form->addField($field);

        $field = new Text();
        $field->name = $field::class;
        $form->addField($field);

        $field = new Textarea();
        $field->name = $field::class;
        $form->addField($field);

        $field = new Time();
        $field->name = $field::class;
        $form->addField($field);

        $field = new Toggle();
        $field->name = $field::class;
        $form->addField($field);

        $field = new TwoFactorCode();
        $field->name = $field::class;
        $form->addField($field);

        return $form;
    }
}
