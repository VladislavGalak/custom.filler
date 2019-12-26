# custom.filler

## Пример использования
><?
>$phraseGen=new \Custom\Filler\Model\TextInput();
>$adjGen=new \Custom\Filler\Model\AdjectiveGenerator($phraseGen);
>$adjGen->generatePhrase('PAINT');
>echo "<pre>";
>print_r($adjGen->getPhrase());
>echo "</pre>";
>?>
