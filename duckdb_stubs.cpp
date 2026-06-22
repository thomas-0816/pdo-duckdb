#include "duckdb.hpp"
#include <cstring>

namespace duckdb {

} // namespace duckdb

extern "C" char *duckdb_variant_get_string(duckdb_vector vec, idx_t row) {
	if (!vec) return NULL;

	auto *variant_vec = reinterpret_cast<duckdb::Vector *>(vec);
	auto value = variant_vec->GetValue(row);

	if (value.IsNull()) return NULL;

	auto str_value = value.DefaultCastAs(duckdb::LogicalType::VARCHAR);
	auto str = str_value.ToString();

	auto *result = (char *)duckdb_malloc(str.size() + 1);
	if (result) {
		memcpy(result, str.c_str(), str.size());
		result[str.size()] = '\0';
	}

	return result;
}
