#include "duckdb.hpp"
#include <cstring>
#include <cstdio>

namespace duckdb {

} // namespace duckdb

static char *variant_blob_to_string(const char *data, uint32_t size, uint8_t type_id) {
	auto type = static_cast<duckdb::VariantLogicalType>(type_id);

	switch (type) {
	case duckdb::VariantLogicalType::VARIANT_NULL:
		return NULL;
	case duckdb::VariantLogicalType::BOOL_TRUE:
	case duckdb::VariantLogicalType::BOOL_FALSE: {
		auto *s = (char *)duckdb_malloc(6);
		if (s) {
			if (type == duckdb::VariantLogicalType::BOOL_TRUE) {
				memcpy(s, "true", 4); s[4] = '\0';
			} else {
				memcpy(s, "false", 5); s[5] = '\0';
			}
		}
		return s;
	}
	case duckdb::VariantLogicalType::INT8: {
		if (size < 1) return NULL;
		int8_t val; memcpy(&val, data, 1);
		auto *s = (char *)duckdb_malloc(20);
		if (s) snprintf(s, 20, "%d", (int)val);
		return s;
	}
	case duckdb::VariantLogicalType::INT16: {
		if (size < 2) return NULL;
		int16_t val; memcpy(&val, data, 2);
		auto *s = (char *)duckdb_malloc(20);
		if (s) snprintf(s, 20, "%d", (int)val);
		return s;
	}
	case duckdb::VariantLogicalType::INT32: {
		if (size < 4) return NULL;
		int32_t val; memcpy(&val, data, 4);
		auto *s = (char *)duckdb_malloc(20);
		if (s) snprintf(s, 20, "%d", (int)val);
		return s;
	}
	case duckdb::VariantLogicalType::INT64: {
		if (size < 8) return NULL;
		int64_t val; memcpy(&val, data, 8);
		auto *s = (char *)duckdb_malloc(30);
		if (s) snprintf(s, 30, "%ld", (long)val);
		return s;
	}
	case duckdb::VariantLogicalType::FLOAT: {
		if (size < 4) return NULL;
		float val; memcpy(&val, data, 4);
		auto *s = (char *)duckdb_malloc(50);
		if (s) snprintf(s, 50, "%g", (double)val);
		return s;
	}
	case duckdb::VariantLogicalType::DOUBLE: {
		if (size < 8) return NULL;
		double val; memcpy(&val, data, 8);
		auto *s = (char *)duckdb_malloc(50);
		if (s) snprintf(s, 50, "%.17g", val);
		return s;
	}
	case duckdb::VariantLogicalType::VARCHAR: {
		auto *s = (char *)duckdb_malloc(size + 1);
		if (s) { memcpy(s, data, size); s[size] = '\0'; }
		return s;
	}
	default:
		fprintf(stderr, "DUCKDB VARIANT: unhandled type %u\n", (unsigned)type_id);
		return NULL;
	}
}

extern "C" char *duckdb_variant_get_string(duckdb_vector vec, idx_t row) {
	if (!vec) return NULL;

	auto *variant_vec = reinterpret_cast<duckdb::Vector *>(vec);

	variant_vec->Flatten(STANDARD_VECTOR_SIZE);

	auto &raw_data   = duckdb::VariantVector::GetData(*variant_vec);
	auto &type_ids   = duckdb::VariantVector::GetValuesTypeId(*variant_vec);
	auto &byte_offs  = duckdb::VariantVector::GetValuesByteOffset(*variant_vec);
	auto &values     = duckdb::VariantVector::GetValues(*variant_vec);

	raw_data.Flatten(STANDARD_VECTOR_SIZE);
	type_ids.Flatten(STANDARD_VECTOR_SIZE);
	byte_offs.Flatten(STANDARD_VECTOR_SIZE);
	values.Flatten(STANDARD_VECTOR_SIZE);

	auto *blob_arr      = duckdb::FlatVector::GetData<duckdb::string_t>(raw_data);
	auto *type_arr      = duckdb::FlatVector::GetData<uint8_t>(type_ids);
	auto *byte_off_arr  = duckdb::FlatVector::GetData<uint32_t>(byte_offs);
	auto *list_entries  = duckdb::FlatVector::GetData<duckdb::list_entry_t>(values);

	if (!blob_arr || !type_arr || !byte_off_arr || !list_entries) return NULL;

	auto entry = list_entries[row];
	if (entry.length == 0) return NULL;

	auto type_id   = type_arr[entry.offset];
	auto byte_off  = byte_off_arr[entry.offset];

	const auto &blob = blob_arr[row];
	auto blob_ptr = blob.GetData();
	auto blob_size = blob.GetSize();

	const char *val_data = blob_ptr + byte_off;
	uint32_t val_size;

	auto variant_type = static_cast<duckdb::VariantLogicalType>(type_id);

	switch (variant_type) {
	case duckdb::VariantLogicalType::VARIANT_NULL:
		return NULL;
	case duckdb::VariantLogicalType::BOOL_TRUE:
	case duckdb::VariantLogicalType::BOOL_FALSE:
		val_size = 0;
		break;
	case duckdb::VariantLogicalType::INT8:
		val_size = 1;
		break;
	case duckdb::VariantLogicalType::INT16:
		val_size = 2;
		break;
	case duckdb::VariantLogicalType::INT32:
	case duckdb::VariantLogicalType::FLOAT:
		val_size = 4;
		break;
	case duckdb::VariantLogicalType::INT64:
	case duckdb::VariantLogicalType::DOUBLE:
	case duckdb::VariantLogicalType::UINT64:
		val_size = 8;
		break;
	case duckdb::VariantLogicalType::INT128:
	case duckdb::VariantLogicalType::UINT128:
	case duckdb::VariantLogicalType::UUID:
	case duckdb::VariantLogicalType::INTERVAL:
	case duckdb::VariantLogicalType::DECIMAL:
		val_size = 16;
		break;
	case duckdb::VariantLogicalType::VARCHAR:
	case duckdb::VariantLogicalType::BLOB: {
		// Decode varint length prefix (base-128, MSB continuation)
		uint32_t str_len = 0;
		uint32_t vi = 0;
		uint8_t shift = 0;
		while (vi < 5) {
			uint8_t byte = (uint8_t)val_data[vi];
			str_len |= (uint32_t)(byte & 0x7F) << shift;
			vi++;
			if ((byte & 0x80) == 0) break;
			shift += 7;
		}
		val_data += vi; // skip past varint
		val_size = str_len;
		break;
	}
	case duckdb::VariantLogicalType::DATE:
	case duckdb::VariantLogicalType::TIME_MICROS:
	case duckdb::VariantLogicalType::TIME_NANOS:
	case duckdb::VariantLogicalType::TIME_MICROS_TZ:
	case duckdb::VariantLogicalType::TIMESTAMP_SEC:
	case duckdb::VariantLogicalType::TIMESTAMP_MILIS:
	case duckdb::VariantLogicalType::TIMESTAMP_MICROS:
	case duckdb::VariantLogicalType::TIMESTAMP_NANOS:
	case duckdb::VariantLogicalType::TIMESTAMP_MICROS_TZ:
		val_size = 8;
		break;
	case duckdb::VariantLogicalType::BITSTRING:
		val_size = 0;
		break;
	default:
		val_size = blob_size - byte_off;
		break;
	}

	return variant_blob_to_string(val_data, val_size, type_id);
}
