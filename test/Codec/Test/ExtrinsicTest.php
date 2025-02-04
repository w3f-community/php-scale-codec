<?php

namespace Codec\Test;

use Codec\Base;
use Codec\ScaleBytes;
use Codec\Types\ScaleInstance;
use PHPUnit\Framework\TestCase;

require_once "const.php";

final class ExtrinsicTest extends TestCase
{
    public function testSampleExtrinsicDecoder ()
    {
        $codec = new ScaleInstance(Base::create());
        $metadataInstant = $codec->process("metadata", new ScaleBytes(Constant::$metadataStaticV13));
        $decodeExtrinsic = $codec->process("Extrinsic", new ScaleBytes("0x280403000b819fc2837a01"), $metadataInstant["metadata"]);
        $this->assertEquals([
            'extrinsic_length' => 10,
            'version' => '04',
            'look_up' => '0300',
            'module_id' => 'Timestamp',
            'call_name' => 'set',
            'params' => [["name" => "now", "type" => "Compact<T::Moment>", "value" => "1625708208001"]],
        ], $decodeExtrinsic);
    }

    public function testSignerExtrinsicDecoder ()
    {
        $generator = Base::create();
        Base::regCustom($generator, ["address" => "MultiAddress"]);
        $codec = new ScaleInstance($generator);
        $metadataInstant = $codec->process("metadata", new ScaleBytes(Constant::$metadataStaticV13));

        // https://polkadot.subscan.io/extrinsic/5857184-1
        $decodeExtrinsic = $codec->process("Extrinsic", new ScaleBytes("0x450284008897e4fdc4b935d9afd1440e2705559c46508024357e255c584efde50c5b6255008ef5c04dd3a898f3ad0d43cb432673ccaadffc7194464be88cef1ab8dc70fbcfd807fdcd3c85630a2b172b7c9016b545a3ca60f4f0f1788f1053869168a1b900c601000005030007c12e8b63d2592412cbbde38e96181551234bb57ec8438c1281e212b5bed72b0bbce3e7a5d001"),
            $metadataInstant["metadata"]);
        $this->assertEquals([
            'extrinsic_length' => 145,
            'version' => '84',
            "account_id" => ["Id" => "8897e4fdc4b935d9afd1440e2705559c46508024357e255c584efde50c5b6255"],
            "signature" => ["Ed25519" => "8ef5c04dd3a898f3ad0d43cb432673ccaadffc7194464be88cef1ab8dc70fbcfd807fdcd3c85630a2b172b7c9016b545a3ca60f4f0f1788f1053869168a1b900"],
            "era" => ["period" => 128, "phase" => 28],
            "nonce" => "0",
            "tip" => "0",
            'look_up' => '0503',
            'module_id' => 'Balances',
            'call_name' => 'transfer_keep_alive',
            "extrinsic_hash" => "0x10febc2b1bfd2f7024bd99685afc810eae81fb03c300a95909bb76cee7670a63",
            'params' => [
                ["name" => "dest", "type" => "<T::Lookup as StaticLookup>::Source", "value" => ["Id" => "07c12e8b63d2592412cbbde38e96181551234bb57ec8438c1281e212b5bed72b"]],
                ["name" => "value", "type" => "Compact<T::Balance>", "value" => "1995648263100"]
            ],
        ], $decodeExtrinsic);
    }


    public function testExtrinsicMismatchMetadata ()
    {
        $codec = new ScaleInstance(Base::create());
        $metadataInstant = $codec->process("metadata", new ScaleBytes(Constant::$metadataStaticV13));

        // kusama block num 9146045 https://kusama.subscan.io/extrinsic/9146045-3
        $this->expectException(\InvalidArgumentException::class);
        $codec->process("Extrinsic", new ScaleBytes("0xd904040b00bc8e8b009c98002408011220d5e7bd0cf571de0ea6a2f3e2a39e8fe813b831c1b4b74a24a606a823137d69831874702f6970342f37392e3133372e36352e3136312f7463702f333033333378742f6970342f3130302e3132332e3134362e36342f7463702f333033333378742f6970342f3130302e3131332e3234332e36342f7463702f333033333374702f6970342f3130302e3130392e3234372e302f7463702f33303333337c782f6970342f3130302e3131332e3230392e3132382f7463702f33303333337c782f6970342f3130302e3131352e3138392e3139322f7463702f3330333333fc3c0000da02000084030000c4bb729273025f2d5c64941f1643167726a0a212c19c20ea83f7a59d6cdba04809ab54362f4cd14838814ccff208049e591da66139999625670f9caf367e0882"),
            $metadataInstant["metadata"]);
    }


    public function testExtrinsicWithMetadataV14 ()
    {
        $generator = Base::create();
        $codec = new ScaleInstance($generator);
        $metadataInstant = $codec->process("metadata", new ScaleBytes(Constant::$metadataStaticV14));
        // rococo block num 133418 https://rococo.subscan.io/extrinsic/133418-2
        $decodeExtrinsic = $codec->process("Extrinsic", new ScaleBytes("0x450284009ed7705e3c7da027ba0583a22a3212042f7e715d3c168ba14f1424e2bc111d0001243196d4bf13f52ae5348f34d2a6aa926fbda6ec206f75669fe224f369266d16b1998d3d519eb7a6e07202b94ef196282c713970aa5a7e0f84fa97271f5f76857502ed0200040000ae8bde916d81d9525267dde07517510be474a76781daa4921bda39e5f8f8a767070010a5d4e8"),
            $metadataInstant["metadata"]);
        $this->assertEquals(json_decode(
            '{"extrinsic_length":145,
                   "version":"84",
                   "account_id":{"Id":"9ed7705e3c7da027ba0583a22a3212042f7e715d3c168ba14f1424e2bc111d00"},
                   "signature":{"Sr25519":"243196d4bf13f52ae5348f34d2a6aa926fbda6ec206f75669fe224f369266d16b1998d3d519eb7a6e07202b94ef196282c713970aa5a7e0f84fa97271f5f7685"},
                   "era":{"period":64,"phase":39},
                   "nonce":"187",
                   "tip":"0",
                   "extrinsic_hash":"0x0b202e15d49ec5cbe8505be3fcfd80d826781af763bf99c790ceaf794050ffa1",
                   "look_up":"0400",
                   "module_id":"Balances",
                   "call_name":"transfer",
                   "params":[{"name":"dest","type":"MultiAddress","value":{"Id":"ae8bde916d81d9525267dde07517510be474a76781daa4921bda39e5f8f8a767"}},
                   {"name":"value","type":"Compact<U128>","value":"1000000000000"}]}',
            true),
            $decodeExtrinsic);
    }
}