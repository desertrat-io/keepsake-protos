use std::{f32, f64, i32, i64};
use std::collections::BTreeMap;

use avro_rs::Schema;
use avro_rs::to_avro_datum;
use avro_rs::types::Value;
use base64::encode;
use failure::Error;

fn main() -> Result<(),Error> {
    let mut tests = BTreeMap::new();

    // Null
    tests.insert(("TYPE_NULL", "null"), (Schema::Null, Value::Null));

    // Boolean
    tests.insert(("TYPE_BOOLEAN", "true"), (Schema::Boolean, Value::Boolean(true)));
    tests.insert(("TYPE_BOOLEAN", "false"), (Schema::Boolean, Value::Boolean(false)));

    // Bytes
    tests.insert(("TYPE_BYTES", "\"\""),(Schema::Bytes, Value::Bytes(Vec::new())));
    tests.insert(
        ("TYPE_BYTES", "\"\\00\\01\\02\\03\\04\\05\\06\\07abcdeABCDE\\n\\t\\r\""),
        (Schema::Bytes, Value::Bytes(b"\x00\x01\x02\x03\x04\x05\x06\x07abcdeABCDE\n\t\r".to_vec()))
    );

    // String
    tests.insert(("TYPE_STRING", "\"\""),(Schema::String, Value::String("".into())));
    tests.insert(
        ("TYPE_STRING", "\"abcdäöü😀_👊\""),
        (Schema::String, Value::String("abcdäöü😀_👊".into()))
    );

    // Int
    tests.insert(("TYPE_INT", "0"), (Schema::Int, Value::Int(0)));
    tests.insert(("TYPE_INT", "1"), (Schema::Int, Value::Int(1)));
    tests.insert(("TYPE_INT", "-1"), (Schema::Int, Value::Int(-1)));
    tests.insert(("TYPE_INT", "2147483647"), (Schema::Int, Value::Int(i32::MAX)));
    tests.insert(("TYPE_INT", "-2147483648"), (Schema::Int, Value::Int(i32::MIN)));

    // Long
    tests.insert(("TYPE_LONG", "0"), (Schema::Long, Value::Long(0)));
    tests.insert(("TYPE_LONG", "1"), (Schema::Long, Value::Long(1)));
    tests.insert(("TYPE_LONG", "-1"), (Schema::Long, Value::Long(-1)));
    tests.insert(("TYPE_LONG", "PHP_INT_MAX"), (Schema::Long, Value::Long(i64::MAX)));
    tests.insert(("TYPE_LONG", "PHP_INT_MIN"), (Schema::Long, Value::Long(i64::MIN)));

    // Float
    tests.insert(("TYPE_FLOAT", "0.0"), (Schema::Float, Value::Float(0.0)));
    tests.insert(("TYPE_FLOAT", "1.0"), (Schema::Float, Value::Float(1.0)));
    tests.insert(("TYPE_FLOAT", "-1.0"), (Schema::Float, Value::Float(-1.0)));
    tests.insert(("TYPE_FLOAT", "9.1345596313477"), (Schema::Float, Value::Float(9.1345596313477)));
    tests.insert(("TYPE_FLOAT", "100000.125"), (Schema::Float, Value::Float(100000.125)));
    tests.insert(("TYPE_FLOAT", "INF"), (Schema::Float, Value::Float(f32::INFINITY)));
    tests.insert(("TYPE_FLOAT", "NAN"), (Schema::Float, Value::Float(f32::NAN)));

    // Double
    tests.insert(("TYPE_DOUBLE", "0.0"), (Schema::Double, Value::Double(0.0)));
    tests.insert(("TYPE_DOUBLE", "1.0"), (Schema::Double, Value::Double(1.0)));
    tests.insert(("TYPE_DOUBLE", "-1.0"), (Schema::Double, Value::Double(-1.0)));
    tests.insert(("TYPE_DOUBLE", "9.1345596313477"), (Schema::Double, Value::Double(9.1345596313477)));
    tests.insert(("TYPE_DOUBLE", "100000.123123123123"), (Schema::Double, Value::Double(100000.123123123123)));
    tests.insert(("TYPE_DOUBLE", "INF"), (Schema::Double, Value::Double(f64::INFINITY)));
    tests.insert(("TYPE_DOUBLE", "NAN"), (Schema::Double, Value::Double(f64::NAN)));


    let definition: Vec<String> = tests.iter()
        .map(|((type_name, val), (schema, value))| -> String {
            let data = encode(&to_avro_datum(schema, value.clone()).unwrap());
            format!("[Primitive::{}, {}, \\base64_decode(\"{}\", true)]", type_name, val, data)
        })
        .collect();

    println!("[\n    {}\n]", definition.join(",\n    "));

    Ok(())
}

